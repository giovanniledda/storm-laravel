<?php

namespace App;

use App\Utils\Utils;
use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function exif_imagetype;
use function explode;
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
use const SECTION_IMAGE_POINTS_OVERVIEW;

class Section extends Model
{

    use DocumentableTrait;

    protected $table = 'sections';

    protected $fillable = [
        'name',
        'section_type',
        'position',
        'code',
        'boat_id'
    ];

    public function getMediaPath($media)
    {
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

    public function generic_documents()
    {
        return $this->documents()->where('type', \Net7\Documents\Document::GENERIC_DOCUMENT_TYPE);
    }

    public function generic_images()
    {
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
        $section = new Section(
            [
                'name' => $faker->numerify('Deck #'),
                'section_type' => $faker->randomElement(
                    [SECTION_TYPE_LEFT_SIDE, SECTION_TYPE_RIGHT_SIDE, SECTION_TYPE_DECK]
                ),
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
     * @param string $filepath
     * @param string|null $type
     * @param string|null $title
     * @param string|null $p_filename
     * @return Document
     */
    public function addImagePhoto(
        string $filepath,
        string $type = null,
        string $title = null,
        string $p_filename = null
    ) {
        // TODO: mettere tutto in una funzione
        $filename = $p_filename ?? Arr::last(explode('/', $filepath));
        $tempFilepath = '/tmp/' . $filename;
        copy($filepath, $tempFilepath);
        $file = new UploadedFile($tempFilepath, $filename, null, null, true);

        $doc = new Document(
            [
                'title' => $title ?? "Image photo for section {$this->id}",
                'file' => $file,
            ]
        );
        $this->addDocumentWithType($doc, $type ? $type : Document::GENERIC_IMAGE_TYPE);

        return $doc;
    }

    /**
     * Given some Task ids, get the related Sections
     *
     * @param array $tasks_ids
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getSectionsStartingFromTasks(array $tasks_ids)
    {
        return Section::query()
            ->select('*')
            ->distinct()
            ->whereIn(
                'id',
                function ($query) use ($tasks_ids) {
                    $query->select('section_id')
                        ->from('tasks')
                        ->whereIn('id', $tasks_ids);
                }
            )
            ->get();
    }

    /**
     * Given a bunch of Task ids, this function filters them belongings to a particular Section
     *
     * @param array $tasks_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOnlyMyTasks(array $tasks_ids)
    {
        return $this->tasks()->whereIn('id', $tasks_ids)->get();
    }

    /**
     *  Give the deck image with all its points
     * @param array $tasks_ids
     * @param int|null $division_factor
     * @return array
     *
     * TODO: funzione troppo lunga, spezza in più parti
     */
    public function drawOverviewImageWithTaskPoints(array $tasks_ids = [], int $division_factor = null)
    {
        ini_set('memory_limit', '-1');
        // immagine del ponte
        $deck_media = $this->generic_images->last();
        // i task di cui vogliamo stampare i pin
        $my_tasks = !empty($tasks_ids) ? $this->getOnlyMyTasks($tasks_ids) : $this->tasks;
        if ($deck_media && count($my_tasks)) {
            $deck_with_pins_f_handler = tmpfile();
            $deck_with_pins_f_path = stream_get_meta_data($deck_with_pins_f_handler)['uri'];

            $tmpfileHandle = tmpfile();
            $final_file_path = stream_get_meta_data($tmpfileHandle)['uri'];

            $deck_img_path = $deck_media->getPathBySize('');
            $bridgeImageInfo = getimagesize($deck_img_path);
            $bridge_w = $bridgeImageInfo[0];
            $bridge_h = $bridgeImageInfo[1];

            // crea un immagine tutta bianca con W e H il doppio di quelle dell'immagine del ponte
            $dst_deck_white_bkg_img = imagecreate($bridge_w * 2, $bridge_h * 2);
            imagecolorallocate($dst_deck_white_bkg_img, 255, 255, 255);

            if (exif_imagetype($deck_img_path) === IMAGETYPE_PNG) {
                // il ponte e' un'immagine png
                $original_deck_img_src = imagecreatefrompng($deck_img_path);
                imagealphablending($original_deck_img_src, false);
                imagesavealpha($original_deck_img_src, true);
            }

            if (exif_imagetype($deck_img_path) === IMAGETYPE_JPEG) {
                // il ponte e' un'immagine jpg
                $original_deck_img_src = imagecreatefromjpeg($deck_img_path);
            }

            // Copy a part of src_im onto dst_im starting at the x,y coordinates src_x, src_y with a width of src_w and a height of src_h.
            // The portion defined will be copied onto the x,y coordinates, dst_x and dst_y.
            // In sostanza qua si copia l'immagine bianca sopra l'immagine trasparente del ponte
            imagecopy(
                $dst_deck_white_bkg_img,
                $original_deck_img_src,
                $bridge_w / 2,
                $bridge_h / 2,
                0,
                0,
                $bridge_w,
                $bridge_h
            );

            // ridimensiono l'immagine del ponte e la fisso ad una larghezza fissa
            $iconInfo = getimagesize($deck_img_path);

            // TODO: spostare tra le prop
            $resize_image = true;
            $resize_pins = false;

            if ($resize_image) {
                $sizeW = $division_factor ? ($bridge_w) / ($division_factor * 2) : 696;  // larghezza del foglio A4 (queste immagini sono create per il doc CorrosionMap)
                $sizeH = $sizeW * ($bridge_h) / ($bridge_w);
            } else {
                $sizeW = $iconInfo[0];
                $sizeH = $iconInfo[1];
            }
            // copio l'immagine del ponte con lo sfondo bianco precedentemente applicato, nel file temporaneo $mapfilePath
            imagepng($dst_deck_white_bkg_img, $deck_with_pins_f_path);

            // ridimensiono l'immagine secondo i calcoli sopra
            $deck_with_pins_resized_img_dest = Utils::resize_image($deck_with_pins_f_path, $sizeW, $sizeH);

            imagealphablending($deck_with_pins_resized_img_dest, false);
            imagesavealpha($deck_with_pins_resized_img_dest, true);

            if (0) { // debug
                $tmpfileHandle2 = tmpfile();
                $final_file_path2 = stream_get_meta_data($tmpfileHandle2)['uri'];

                imagepng($deck_with_pins_resized_img_dest, $final_file_path2);
                $this->addImagePhoto(
                    $final_file_path2,
                    SECTION_IMAGE_POINTS_OVERVIEW.'Tmp',
                    "Deck with pins image for Section #{$this->id}",
                    'deck_with_pins_resized_img_dest_tmp.png'
                );
            }

            /** @var Task $task */
            foreach ($my_tasks as $task) {
                // creo l'immagine PNG del pin del Task
                $pinPath = $task->getIcon(null, null, 'Active', true);
                $iconInfo = getimagesize($pinPath);
                if ($resize_pins) {
                    $new_w = 20;
                    $new_h = 32;
//                    $pin_png_image_src = Utils::resize_image($pinPath, $new_w, $new_h);
                    $pin_png_image_src_orig = imagecreatefrompng($pinPath);
                    $pin_png_image_src = Utils::getPNGImageResized($pin_png_image_src_orig, $new_w, $new_h);
                } else {
                    $new_w = $iconInfo[0]; // 20;
                    $new_h = $iconInfo[1]; // 48;

                    $pin_png_image_src = imagecreatefrompng($pinPath);
                    imagealphablending($pin_png_image_src, false);
                    imagesavealpha($pin_png_image_src, true);
                }

                // Credo che questi calcoli siano fatti per invertire coordinate X e Y (è così, mi ha detto @miscali)
                $x = $bridge_w / 2 + $task->y_coord;
                $y = ($bridge_h - $task->x_coord) + $bridge_h / 2;
                // ... e per riposizionare X e Y del pin in base al ridimensionamento dell'immagine
                $xx = ($x * $sizeW) / ($bridge_w * 2);
                $yy = ($y * $sizeH) / ($bridge_h * 2);

                // copio il pin  sull'immagine del deck
//                imagecopymerge($deck_with_pins_resized_img_dest, $pin_png_image_src, $xx - $new_w / 2, $yy - $new_h, 0, 0, $new_w, $new_h, 100);
                Utils::imagecopymerge_alpha(
                    $deck_with_pins_resized_img_dest,
                    $pin_png_image_src,
                    $xx - $new_w / 2,
                    $yy - $new_h,
                    0,
                    0,
                    $new_w,
                    $new_h,
                    100
                );

                imagealphablending($deck_with_pins_resized_img_dest, false);
                imagesavealpha($deck_with_pins_resized_img_dest, true);
                imagedestroy($pin_png_image_src);
            }

            $crop_final_image = true;
            if ($crop_final_image) {
                $crop_w = ($sizeW / 2) * 1.5;
                $crop_h = ($sizeH / 2) * 1.5;
                $im2 = imagecrop(
                    $deck_with_pins_resized_img_dest,
                    [
                        'x' => ($sizeW / 2) - $crop_w / 2,
                        'y' => ($sizeH / 2) - $crop_h / 2,
                        'width' => $crop_w,
                        'height' => $crop_h
                    ]
                );
                if ($im2 !== false) {
                    imagealphablending($im2, false);
                    imagesavealpha($im2, true);
                    imagepng($im2, $final_file_path);
                    imagedestroy($im2);
                } else {
                    imagealphablending($deck_with_pins_resized_img_dest, false);
                    imagesavealpha($deck_with_pins_resized_img_dest, true);
                    imagepng($deck_with_pins_resized_img_dest, $final_file_path);
                }
            } else {
                imagealphablending($deck_with_pins_resized_img_dest, false);
                imagesavealpha($deck_with_pins_resized_img_dest, true);
                imagepng($deck_with_pins_resized_img_dest, $final_file_path);
            }

            $this->addImagePhoto(
                $final_file_path,
                SECTION_IMAGE_POINTS_OVERVIEW,
                "Deck with pins image for Section #{$this->id}",
                'deck_with_pins_resized_img_dest.png'
            );

            fclose($deck_with_pins_f_handler);

            imagedestroy($original_deck_img_src);
            imagedestroy($dst_deck_white_bkg_img);

            fclose($tmpfileHandle); //this removes the tempfile
            return $final_file_path;
        }
    }

    /**
     * @return string
     */
    public function getPointsImageOverview()
    {
        $document = $this->documents->where('type', SECTION_IMAGE_POINTS_OVERVIEW)->last();
        if ($document) {
            $media = $document->getRelatedMedia();
            return $media->getPath();
        } else {
            return '';
        }
    }
}
