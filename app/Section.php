<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function exif_imagetype;
use function fclose;
use function getimagesize;
use function imagealphablending;
use function imagecolorallocate;
use function imagecopy;
use function imagecopymerge;
use function imagecreate;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecrop;
use function imagedestroy;
use function imagepng;
use function imagesavealpha;
use function ini_set;
use function is_dir;
use function is_file;
use function mkdir;
use function storage_path;
use function stream_get_meta_data;
use function tmpfile;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const IMAGETYPE_JPEG;
use const IMAGETYPE_PNG;

class Section extends Model
{

    use DocumentableTrait;

    protected $table = 'sections';

    protected $fillable = [
      'name', 'section_type', 'position', 'code', 'boat_id'
    ];

    public function getMediaPath($media){

        $document = $media->model;
        $media_id = $media->id;
        $boat_id = $this->id;

        $section_id = $this->id;
        $boat = $this->boat;
        $boat_id = $boat->id;
        $path = 'boats' . DIRECTORY_SEPARATOR . $boat_id . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR . $section_id .
                    DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;

    }

    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function subsections()
    {
        return $this->hasMany('App\Subsection');
    }

//    public function tasks()
//    {
//        return $this->hasManyThrough('App\Task', 'App\Subsection');
//    }

    public function tasks()
    {
        return $this->hasMany('App\Task');
    }

    public function map_image()
    {
        return $this->morphOne('Net7\Documents\Document', 'documentable');
    }

    public function generic_documents(){
        return $this->documents()->where('type', \Net7\Documents\Document::GENERIC_DOCUMENT_TYPE);
    }

    public function generic_images(){
        return $this->documents()->where('type', \Net7\Documents\Document::GENERIC_IMAGE_TYPE);
    }

    /**
     * Creates a Section using some fake data and some others that have sense
     *
     * @param Faker $faker
     * @param Boat $boat
     *
     * @return Section $section
     */
    public static function createSemiFake(Faker $faker, Boat $boat = null)
    {
        $section = new Section([
                'name' => $faker->numerify('Deck #'),
                'section_type' => $faker->randomElement([SECTION_TYPE_LEFT_SIDE, SECTION_TYPE_RIGHT_SIDE, SECTION_TYPE_DECK]),
                'position' => $faker->randomDigitNotNull(),
                'code' => $faker->lexify('???-???'),
                'boat_id' => $boat ? $boat->id : null
            ]
        );
        $section->save();
        return $section;
    }


    /**
     * Adds an image as a generic_image Net7/Document
     *
     */
    public function addImagePhoto(string $filepath, string $type = null)
    {
        // TODO: mettere tutto in una funzione
        $f_arr = explode('/', $filepath);
        $filename = Arr::last($f_arr);
        $tempFilepath = '/tmp/' . $filename;
        copy('./storage/seeder/' . $filepath, $tempFilepath);
        $file = new UploadedFile($tempFilepath, $filename, null, null, true);

        $doc = new Document([
            'title' => "Image photo for section {$this->id}",
            'file' => $file,
        ]);
        $this->addDocumentWithType($doc, $type ? $type : Document::GENERIC_IMAGE_TYPE);

        return $doc;
    }

    /**
     *  Give the deck image with all its points
     */
    public function drawOverviewImageWithTaskPoints()
    {
        ini_set('memory_limit', '-1');
        $bridgeMedia = $this->generic_images->last();
//        SECTION_IMAGE_POINTS_OVERVIEW
        if ($bridgeMedia && $this->tasks()->count()) {
            $bridgeImagePath = $bridgeMedia->getPathBySize('');
            /** @var Task $task */
            foreach ($this->tasks as $task) {

                $map_dir = storage_path() . DIRECTORY_SEPARATOR . '/tasks/';
                if (!is_dir($map_dir)) {
                    mkdir($map_dir);
                }

                $tmpfilePath = storage_path() . DIRECTORY_SEPARATOR . '/tasks/' . DIRECTORY_SEPARATOR . $task->id . '_map.png';
                if (is_file($tmpfilePath)) {
                    unlink($tmpfilePath);
                }

                $mapfileHandle = tmpfile();
                $mapfilePath = stream_get_meta_data($mapfileHandle)['uri'];

                $tmpfileHandle = tmpfile();
                $tmpfilePath = stream_get_meta_data($tmpfileHandle)['uri'];

                $bridgeImagePath = $bridgeMedia->getPathBySize('');
                $bridgeImageInfo = getimagesize($bridgeImagePath);
                $image = imagecreate($bridgeImageInfo[0] * 2, $bridgeImageInfo[1] * 2);
                imagecolorallocate($image, 255, 255, 255);

                if (exif_imagetype($bridgeImagePath) === IMAGETYPE_PNG) {
                    // il ponte e' un'immagine png
                    $dest = imagecreatefrompng($bridgeImagePath);
                    imagealphablending($dest, false);
                    imagesavealpha($dest, true);
                }

                if (exif_imagetype($bridgeImagePath) === IMAGETYPE_JPEG) {
                    // il ponte e' un'immagine jpg
                    $dest = imagecreatefromjpeg($bridgeImagePath);
                }

                imagecopy($image, $dest, $bridgeImageInfo[0] / 2, $bridgeImageInfo[1] / 2, 0, 0, $bridgeImageInfo[0], $bridgeImageInfo[1]);

                try {
                    $pinPath = $task->getIcon();
                    $iconInfo = getimagesize($pinPath);
                    $src = imagecreatefrompng($pinPath);
                    imagealphablending($src, false);
                    imagesavealpha($src, true);

                    // ridimensiono l'immagine del ponte e la fisso ad una larghezza fissa
                    $sizeW = 696;  // larghezza del foglio A4 (queste immagini sono create per il doc CorrosionMap)
                    $sizeH = $sizeW * ($bridgeImageInfo[1] * 2) / ($bridgeImageInfo[0] * 2);

                    $x = $bridgeImageInfo[0] / 2 + $task->y_coord;
                    $y = ($bridgeImageInfo[1] - $task->x_coord) + $bridgeImageInfo[1] / 2;

                    $xx = ($x * $sizeW) / ($bridgeImageInfo[0] * 2);
                    $yy = ($y * $sizeH) / ($bridgeImageInfo[1] * 2);

                    // imagepng($image, $map);
                    imagepng($image, $mapfilePath);

                    // $el = $this->resize_image($map, $sizeW, $sizeH);
                    $el = $task->resize_image($mapfilePath, $sizeW, $sizeH);

                    imagealphablending($el, false);
                    imagesavealpha($el, true);

                    fclose($mapfileHandle);
                    imagecopymerge($el, $src, $xx - $iconInfo[0] / 2, $yy - $iconInfo[1], 0, 0, $iconInfo[0], $iconInfo[1], 100);

                    imagealphablending($el, false);
                    imagesavealpha($el, true);

                    imagedestroy($dest);
                    imagedestroy($src);
                    imagedestroy($image);

                    // no...devo salvare su un file tmp che vado a rimpiazzare ogni volta...solo alla fine salvo nella section

                    $this->addFileOrUpdateDocumentWithType($tmpfilePath, $this::CORROSION_MAP_DOCUMENT_TYPE, 'corrosion_map');

                    fclose($tmpfileHandle); //this removes the tempfile

                    return ['success' => true, 'H' => $sizeH, 'W' => $sizeW];

                } catch (\Exception $exc) {
                    return ['success' => false, 'error' => $exc->getMessage()];
                }
            }
        }
    }
}
