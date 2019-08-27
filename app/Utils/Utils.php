<?php

namespace App\Utils;

use App\Profession;
use App\Project;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function is_null;
use const PERMISSION_ADMIN;
use const PERMISSION_WORKER;
use const PROJECT_STATUS_CLOSED;
use const ROLE_ADMIN;
use const ROLE_BOAT_MANAGER;
use const ROLE_WORKER;
use const USER_PHONE_TYPE_FIXED;
use const USER_PHONE_TYPE_MOBILE;
use Webpatser\Countries\Countries;

class Utils
{
    /**
     * Get an incremental ID for factories use
     *
     * @return \Generator
     */
    public static function autoIncrement()
    {
        for ($i = 0; $i < 1000; $i++) {
            yield $i;
        }
    }

    public static function getFakeStormEmail($username = null)
    {
        $progressive = self::autoIncrement();
        $faker = Faker::create();
        if (is_null($username)) {
            $username = $faker->userName;
        }

        do {
            $email = $username.$progressive->current().STORM_EMAIL_SUFFIX;
            // Must not already exist in the `email` column of `users` table
            $validator = Validator::make(['email' => $email], ['email' => 'unique:users']);
            $progressive->next();
        } while($validator->fails());

        return $email;
    }

    public static function getAdmins() {
        return User::permission(PERMISSION_ADMIN)->get();
//        return User::role(ROLE_ADMIN)->get();
    }

    public static function getAllBoatManagers() {
        return User::permission(PERMISSION_BOAT_MANAGER)->get();
//        return User::role(ROLE_BOAT_MANAGER)->get();
    }

    public static function getAllWorkers() {
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
    public static function replacePlaceholders($string = '', $placeholders = [])
    {
        return strtr($string, $placeholders);
    }

    /**
     * Returns a Response with JSONAPI header
     *
     */
    public static function renderStandardJsonapiResponse($data, $code)
    {
        $resp = Response($data, $code);
        $resp->header('Content-Type', 'application/vnd.api+json');
        return $resp;
    }

    /**
     * Get the list of countries for @countries component
     *
     */
    public static function getCountriesList()
    {
        return Countries::orderBy('name')
            ->whereNotNull('name')
            ->pluck('name', 'iso_3166_2');
    }

    /**
     * Get the list of telephone types defined in constants
     *
     */
    public static function getPhoneTypes()
    {
        return [USER_PHONE_TYPE_MOBILE => USER_PHONE_TYPE_MOBILE,
                USER_PHONE_TYPE_FIXED => USER_PHONE_TYPE_FIXED];
    }


    /**
     * Get the list of professions for @stormprofessions component
     *
     */
    public static function getStormProfessionsList()
    {
        return Profession::orderBy('name')
            ->where('is_storm', 1)
            ->whereNotNull('name')
            ->pluck('name', 'id');
    }

    /**
     * Get the list of professions for @stormprofessions component
     *
     */
    public static function getItemsPerPage()
    {
        return \Config::get('app.page_items');
    }

    /**
     * Get the list of projects for @projects component
     *
     */
    public static function getActiveProjectsList()
    {
        $projs = DB::table('projects')
            ->leftJoin('boats', 'projects.boat_id', '=', 'boats.id')
            ->leftJoin('sites', 'projects.site_id', '=', 'sites.id')
            ->where('projects.project_status', '<>', PROJECT_STATUS_CLOSED)
            ->select('sites.name as sname', 'boats.name as bname','projects.name as pname', 'projects.id')
            ->orderBy('bname')
            ->get();

        $results = [];
        foreach ($projs->all() as $p) {
            $results[$p->id] = __(':pname #:id), Boat :bname [site: :sname]', [
                'id' => $p->id,
                'bname' => $p->bname,
                'sname' => $p->sname,
                'pname' => $p->pname]
            );
        }
        return $results;
    }
    /**
     * renderizza una risposta standard api dati gli errori di validazione. 
     * @param type $errors
     * @return array
     */
    public static function renderDocumentErrors($errors) {
        $e = [];
        foreach ($errors as $error) {
            array_push($e, [
                'status' => 422,
                'title' => "Unprocessable Entity",
                'detail'=>$error,
                'source'=>['source'=>['pointer'=> '/data']]
            ]);
        }
        return $e;
    }
    
    /**
     * ritorna una risposta jsonApi standard per la creazione dei documenti
     * @param type $resource
     * @param type $doc
     */
    public static function renderDocumentResponse($resource, $doc) {
          $ret = ['data' => [
                    'type' => $resource,
                    'id' => $doc->id,
                    'attributes' => [
                        'name' => $doc->title, 
                        'created-at' => $doc->created_at,
                        'updated-at' => $doc->updated_at
                    ]
            ]];
          return $ret;
    }
    
    /**
     * Ritorna la query SQL generata da eloquent.
     * @param type $queryBuilder
     * @return type
     */
    public static function getSql($queryBuilder) {
        $query = str_replace(array('?'), array('\'%s\''), $queryBuilder->toSql());
        $query = vsprintf($query, $queryBuilder->getBindings());
        return  $query;
    }

    /**
     * Chiama la abort inserendo un codice di errore specifico a seconda dei casi
     *
     * @param int $http_status_code
     * @param null $internal_error
     */
    public static function abortWithInternalError($http_status_code = 500, $internal_error = null)
    {
        
    }
    
}
