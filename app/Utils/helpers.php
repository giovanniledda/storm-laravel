<?php

namespace App\Utils;

use App\Models\Profession;
use App\Models\User;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Webpatser\Countries\Countries;
use function __;
use function abort;
use function abs;
use function array_combine;
use function array_map;
use function array_pad;
use function array_shift;
use function array_walk;
use function ceil;
use function count;
use function file;
use function file_exists;
use function getimagesize;
use function htmlspecialchars;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecopy;
use function imagecopymerge;
use function imagecopyresampled;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagefilledrectangle;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function is_null;
use function response;
use function str_replace;
use function throw_if;
use function vsprintf;
use const HTTP_412_ADD_UPD_ERROR_MSG;
use const HTTP_412_DEL_UPD_ERROR_MSG;
use const HTTP_412_EXCEPTION_ERROR_MSG;
use const PERMISSION_ADMIN;
use const PERMISSION_BOAT_MANAGER;
use const PERMISSION_WORKER;
use const PROJECT_STATUS_CLOSED;
use const STORM_EMAIL_SUFFIX;
use const USER_PHONE_TYPE_FIXED;
use const USER_PHONE_TYPE_MOBILE;

/**
 * Get an incremental ID for factories use
 *
 * @return \Generator
 */
function autoIncrement()
{
    for ($i = 0; $i < 1000; $i++) {
        yield $i;
    }
}

function getFakeStormEmail($username = null)
{
    $progressive = autoIncrement();
    $faker = Faker::create();
    if (is_null($username)) {
        $username = $faker->userName;
    }

    do {
        $email = $username . $progressive->current() . STORM_EMAIL_SUFFIX;
        // Must not already exist in the `email` column of `users` table
        $validator = Validator::make(['email' => $email], ['email' => 'unique:users']);
        $progressive->next();
    } while ($validator->fails());

    return $email;
}

function getAdmins()
{
    return User::permission(PERMISSION_ADMIN)->get();
//        return User::role(ROLE_ADMIN)->get();
}

function getAllBoatManagers()
{
    return User::permission(PERMISSION_BOAT_MANAGER)->get();
//        return User::role(ROLE_BOAT_MANAGER)->get();
}

function getAllWorkers()
{
    return User::permission(PERMISSION_WORKER)->get();
//        return User::role(ROLE_WORKER)->get();
}

/**
 * An alias for the strstr function
 *
 * @param string $string
 * @param array $placeholders
 * @return string
 */
function replacePlaceholders($string = '', $placeholders = [])
{
    return strtr($string, $placeholders);
}

/**
 * Returns a Response with JSONAPI header
 */
function renderStandardJsonapiResponse($data, $code)
{
    $resp = Response($data, $code);
    $resp->header('Content-Type', 'application/vnd.api+json');

    return $resp;
}

/**
 * Get the list of countries for @countries component
 */
function getCountriesList()
{
    return Countries::orderBy('name')
        ->whereNotNull('name')
        ->pluck('name', 'iso_3166_2');
}

/**
 * Get the list of telephone types defined in constants
 */
function getPhoneTypes()
{
    return [USER_PHONE_TYPE_MOBILE => USER_PHONE_TYPE_MOBILE,
        USER_PHONE_TYPE_FIXED => USER_PHONE_TYPE_FIXED,];
}

/**
 * Get the list of professions for @stormprofessions component
 */
function getStormProfessionsList()
{
    return Profession::orderBy('name')
        ->where('is_storm', 1)
        ->whereNotNull('name')
        ->pluck('name', 'id');
}

/**
 * Get the list of professions for @stormprofessions component
 */
function getItemsPerPage()
{
    return \Config::get('app.page_items');
}

/**
 * Get the list of projects for @projects component
 */
function getActiveProjectsList()
{
    $projs = DB::table('projects')
        ->leftJoin('boats', 'projects.boat_id', '=', 'boats.id')
        ->leftJoin('sites', 'projects.site_id', '=', 'sites.id')
        ->where('projects.project_status', '<>', PROJECT_STATUS_CLOSED)
        ->select('sites.name as sname', 'boats.name as bname', 'projects.name as pname', 'projects.id')
        ->orderBy('bname')
        ->get();

    $results = [];
    foreach ($projs->all() as $p) {
        $results[$p->id] = __(':pname #:id), Boat :bname [site: :sname]', [
                'id' => $p->id,
                'bname' => $p->bname,
                'sname' => $p->sname,
                'pname' => $p->pname,]
        );
    }

    return $results;
}

/**
 * @param $queryBuilder
 * @return string
 */
function getSql($queryBuilder)
{
    $query = str_replace(['?'], ['\'%s\''], $queryBuilder->toSql());
    $query = vsprintf($query, $queryBuilder->getBindings());

    return $query;
}

/**
 * Esamina l'eccezione e a seconda del motivo che l'ha scatenata determina un abort differente
 *
 * @param Exception $exception
 */
function catchIntegrityConstraintViolationException(Exception $exception)
{
    if (Str::contains($exception->getMessage(), 'Integrity constraint violation')) {
        if (Str::contains($exception->getMessage(), '1451')) {
            abort(412, __(HTTP_412_DEL_UPD_ERROR_MSG));
        }
        if (Str::contains($exception->getMessage(), '1452')) {
            abort(412, __(HTTP_412_ADD_UPD_ERROR_MSG));
        }
    }
    abort(412, __(HTTP_412_EXCEPTION_ERROR_MSG, ['exc_msg' => $exception->getMessage()]));
}

/**
 * Restituisce una response di errore JSONAPI compliant
 * @param int $http_status_code
 * @param int $internal_error
 * @param null $title
 * @param null $message
 * @return \Illuminate\Http\JsonResponse
 */
function jsonAbortWithInternalError($http_status_code = 500, $internal_error = 500, $title = null, $message = null)
{
    $h = ['Content-Type' => 'application/vnd.api+json'];
    $error = [
        'status' => $http_status_code,
        'code' => $internal_error,];

    if ($title) {
        $error['title'] = $title;
    }

    if ($message) {
        $error['detail'] = $message;
    }

    return response()->json(['errors' => $error], (string)$http_status_code, $h);
}

/**
 * Dato un messaggio di errore, restituisce il codice interno specifico
 * ref: https://net7.codebasehq.com/projects/storm/notebook/HTTP%20STATUSES.md
 *
 * @param string $internal_error_message
 *
 * @return int
 */
function convertMessageToInternalErrorCode($internal_error_message = null)
{
    switch ($internal_error_message) {
        case HTTP_412_DEL_UPD_ERROR_MSG:
            return 100;
        case HTTP_412_ADD_UPD_ERROR_MSG:
            return 110;
        case HTTP_412_EXCEPTION_ERROR_MSG:
            return 120;
        default:
            return 500;
    }
}

/**
 * Ridimensiona un'immagine da un path
 * @param $file
 * @param $w
 * @param $h
 * @param $crop
 * @return mixed
 */
function resize_image($file, $w, $h, $crop = false)
{
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width - ($width * abs($r - $w / $h)));
        } else {
            $height = ceil($height - ($height * abs($r - $w / $h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w / $h > $r) {
            $newwidth = $h * $r;
            $newheight = $h;
        } else {
            $newheight = $w / $r;
            $newwidth = $w;
        }
    }
    $src = imagecreatefrompng($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

/**
 * https://stackoverflow.com/a/279310/470749
 *
 * @param resource $image
 * @param int $newWidth
 * @param int $newHeight
 * @return resource
 */
function getPNGImageResized($image, int $newWidth, int $newHeight)
{
    $newImg = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($newImg, false);
    imagesavealpha($newImg, true);
    $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
    imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
    $src_w = imagesx($image);
    $src_h = imagesy($image);
    imagecopyresampled($newImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $src_w, $src_h);

    return $newImg;
}

/**
 * https://www.php.net/manual/en/function.imagecopymerge.php#92787
 *
 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
 * by Sina Salek
 *
 * Bugfix by Ralph Voigt (bug which causes it
 * to work only for $src_x = $src_y = 0.
 * Also, inverting opacity is not necessary.)
 * 08-JAN-2011
 *
 **/
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    // creating a cut resource
    $cut = imagecreatetruecolor($src_w, $src_h);

    // copying relevant section from background to the cut resource
    imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

    // copying relevant section from watermark to the cut resource
    imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

    // insert cut resource to destination image
    imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
}

/**
 * @param $header
 * @param $records
 * @return Writer
 * @throws \League\Csv\CannotInsertRecord
 */
function createCsvFileFromHeadersAndRecords($header, $records)
{
    //load the CSV document from a string
    $csv = Writer::createFromString();

    //insert the header
    $csv->insertOne($header);

    //insert all the records
    $csv->insertAll($records);

    return $csv;
}

// "Presa" dal plugin net7 environmental measurements.
//  TODO: refact...le funzioni sono le stesse ma alcune parti della app la utilizzano a prescindere dal plugin quindi va messa qua
function convertCsvInAssociativeArray($filename)
{
    throw_if(!file_exists($filename), 'Exception', "CSV file not found on $filename!");
    $csv = array_map('str_getcsv', file($filename));
    if (!empty($csv)) {
        $cols = count($csv[0]);
        array_walk($csv, function(&$a) use ($csv, $cols) {
            $a = array_pad($a, $cols, '-');
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header
    }
    return $csv;
}

function sanitizeTextsForPlaceholders($text)
{
    return htmlspecialchars($text);
}

