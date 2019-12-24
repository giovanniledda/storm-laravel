<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Net7\DocsGenerator\Utils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Observers\TaskObserver;
use Spatie\ModelStatus\HasStatuses;
use Venturecraft\Revisionable\RevisionableTrait;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use Faker\Generator as Faker;
use function explode;
use function in_array;
use function is_object;
use const PROJECT_STATUS_CLOSED;
use const TASKS_STATUS_COMPLETED;
use const TASKS_STATUS_DENIED;

class Task extends Model
{

    use RevisionableTrait,
        HasStatuses,
        DocumentableTrait,
        SoftDeletes;

    protected $table = 'tasks';
    protected $fillable = [
        'number',
        'title',
        'task_status',
        'description',
        'estimated_hours',
        'worked_hours',
        'for_admins',
        'project_id',
        'section_id',
        'intervent_type_id',
        'author_id',
        'subsection_id',
        'x_coord',
        'y_coord',
        'is_open',
        'is_private',
        'bridge_position',
    ];
    private $min_x;
    private $max_x;
    private $min_y;
    private $max_y;

    public const CORROSION_MAP_DOCUMENT_TYPE = 'corrosion_map';

    protected $shouldUseRevision = false;

    /**
     * @param mixed $min_x
     * @return Task
     */
    public function setMinX($min_x)
    {
        $this->min_x = $min_x;
        return $this;
    }

    /**
     * @param mixed $max_x
     * @return Task
     */
    public function setMaxX($max_x)
    {
        $this->max_x = $max_x;
        return $this;
    }

    /**
     * @param mixed $min_y
     * @return Task
     */
    public function setMinY($min_y)
    {
        $this->min_y = $min_y;
        return $this;
    }

    /**
     * @param mixed $max_y
     * @return Task
     */
    public function setMaxY($max_y)
    {
        $this->max_y = $max_y;
        return $this;
    }

    public function getMediaPath($media)
    {

        $document = $media->model;
        $media_id = $media->id;

        $project = $this->project;
        $project_id = $project->id;
        $task_id = $this->id;
        $path = 'projects' . DIRECTORY_SEPARATOR . $project_id . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR .
            $task_id . DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;
    }

    /*
      $task = $model;
      $project = $task->project;
      $project_id = $project->id;
      $task_id = $task->id;
      $path .= 'projects' . DIRECTORY_SEPARATOR . $project_id . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR .
      $task_id . DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;
     */

    /**
     * Scope a query to only include not private tasks if current user is not storm.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', '!=', 1);
    }

    /**
     * Scope a query to only include not private tasks if current user is not storm.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', '=', 1);
    }

    /**
     * @param $id
     * @return Task|null
     */
    public static function findPublic($id)
    {
        return Task::public()->where('id', '=', $id)->first();
    }

    protected static function boot()
    {
        parent::boot();

        Task::observe(TaskObserver::class);

        // se utente non è is_storm, non vede i task privati
//        static::addGlobalScope(new VisibilityScope());
    }

    public function intervent_type()
    {
        return $this->belongsTo('App\TaskInterventType');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function getProjectBoat()
    {
//        $this->hasOneThrough('App\Boat','App\Project'); // così non funziona perché va a cercare 'projects.task_id' in 'field list' (SQL: select `boats`.*, `projects`.`task_id` as `laravel_through_key` from `boats` inner join `projects` on `projects`.`id` = `boats`.`project_id` where `projects`.`task_id` = 13 limit 1)'
        return $this->project ? $this->project->boat : null;
    }

    public function subsection()
    {
        return $this->belongsTo('App\Subsection');
    }

    public function section()
    {
        return $this->belongsTo('App\Section');
    }

    public function author()
    {
        return $this->belongsTo('App\User');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function history()
    {
        return $this->morphMany('App\History', 'historyable');
    }

    public function taskIntervents()
    {
        return $this->hasOne('App\TaskInterventType');
//        return $this->hasOneThrough('App\Site', 'App\Project');  // NON funziona perché i progetti sono "many" e il site è "one"
    }

    public function getProjectUsers()
    {
        $proj = $this->project;
        if (is_object($proj)) {
            $users = $proj->users;
            if (!empty($users)) {
                return $users;
            }
        }
        return [];
    }

    /**
     * @return array
     *
     * Restituisce gli utenti (e contiene la logica per recuperarli) che devono ricevere una notifica legata agi eventi del Task
     */
    public function getUsersToNotify()
    {

// aggiungere qua altra logica, se serve (tipo filtri sui ruoli, etc)
//        return StormUtils::getAllBoatManagers();
        return $this->getProjectUsers();
    }

    /**
     * Creates a Task using some fake data and some others that have sense
     *
     * @param Faker $faker
     * @param Project $proj
     * @param Section $sect
     * @param Subsection $ssect
     * @param User $author
     * @param TaskInterventType $type
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     *
     * @return Task $t
     */
    public static function createSemiFake(Faker $faker, Project $proj = null, Section $sect = null, Subsection $ssect = null, User $author = null, TaskInterventType $type = null)
    {

        $status = $faker->randomElement(TASKS_STATUSES);
        $is_open = is_object($proj) ? ($proj->project_status != PROJECT_STATUS_CLOSED) : !in_array($status, [TASKS_STATUS_COMPLETED, TASKS_STATUS_DENIED]);
        $t = new Task([
                'number' => $faker->randomDigitNotNull(),
                'title' => $faker->sentence(),
                'description' => $faker->text(),
                'estimated_hours' => $faker->randomFloat(1, 0, 100),
                'worked_hours' => $faker->randomFloat(1, 0, 100),
                'x_coord' => $faker->randomFloat(2, 1119.29, 1159.29), // scostarsi del 5% dal punto 1139.29
                'y_coord' => $faker->randomFloat(2, 267.95, 307.95), // scostarsi del 5% dal punto  287.95
                'task_status' => $status, //$faker->randomElement(TASKS_STATUSES),
                'is_open' => $is_open, //$faker->randomElement([1, 0]),
                'project_id' => $proj ? $proj->id : null,
                'section_id' => $sect ? $sect->id : null,
                'subsection_id' => $ssect ? $ssect->id : null,
                'author_id' => $author ? $author->id : null,
                'intervent_type_id' => $type ? $type->id : null,
            ]
        );
        $t->save();
        $t->setStatus($status);

        return $t;
    }

    public function updateXYCoordinates(Faker &$faker)
    {
        $this->update([
            'x_coord' => $faker->randomFloat(2, $this->min_x ? $this->min_x : 1119.29, $this->max_x ? $this->max_x : 1159.29), // scostarsi del 5% dal punto 1139.29
            'y_coord' => $faker->randomFloat(2, $this->min_y ? $this->min_y : 267.95, $this->max_y ? $this->max_y : 307.95), // scostarsi del 5% dal punto  287.95
        ]);
    }

    /**
     * Adds an image as a generic_image Net7/Document
     *
     */
    public function addDamageReportPhoto(string $filepath, string $type = null)
    {
        // mettere tutto in una funzione
        $f_arr = explode('/', $filepath);
        $filename = Arr::last($f_arr);
        $tempFilepath = '/tmp/' . $filename;
        copy('./storage/seeder/' . $filepath, $tempFilepath);
        $file = new UploadedFile($tempFilepath, $filename, null, null, true);

        $doc = new Document([
            'title' => "Damage photo for task {$this->id}",
            'file' => $file,
        ]);
        $this->addDocumentWithType($doc, $type ? $type : Document::GENERIC_IMAGE_TYPE);

        return $doc;
    }

    /**
     * Retrieve iamge's path
     *
     * @return string
     */
    public function getAdditionalPhotoPath()
    {
        return $this->getDocumentMediaFilePath(Document::ADDITIONAL_IMAGE_TYPE, 'report-large');
    }

    /**
     * Retrieve iamge's path
     *
     * @return string
     */
    public function getDetailedPhotoPaths()
    {
        return $this->getAllDocumentsMediaFilePathArray(Document::DETAILED_IMAGE_TYPE, 'report');
    }

    public function generateBridgePositionFileFromBase64()
    {
        $base64 = $this->bridge_position;
        $handle = tmpfile();
        $path = stream_get_meta_data($handle)['uri'];
        $data = explode(',', $base64);

        fwrite($handle, base64_decode($data[1]));
        fseek($handle, 0);


        $pngPath = $path . ".jpg";

        shell_exec("convert " . $path . " " . $pngPath);

        return [
            'path' => $pngPath,
            'handle' => $handle
        ];
    }

    public function removeTempFileByHandle($handle)
    {
        fclose($handle); // this removes the file
    }

    public function getCorrosionMapFilePath()
    {
        $document = $this->documents->where('type', self::CORROSION_MAP_DOCUMENT_TYPE)->first();
        if ($document) {
            $media = $document->getRelatedMedia();
            return $media->getPath();
        } else {
            return '';
        }
    }

    public function updateMap()
    {
        $task = $this;
        ini_set('memory_limit', '-1');

        $map_dir = storage_path() . DIRECTORY_SEPARATOR . '/tasks/';
        if (!is_dir($map_dir)) {
            mkdir($map_dir);
        }

        $tmpfilePath = storage_path() . DIRECTORY_SEPARATOR . '/tasks/' . DIRECTORY_SEPARATOR . $task->id . '_map.png';
        if (is_file($tmpfilePath)) {
            unlink($tmpfilePath);
        }

        // $map = $map_dir.'map_'.$task->id.'.png';


        $mapfileHandle =  tmpfile();
        $mapfilePath = stream_get_meta_data($mapfileHandle)['uri'];



        $tmpfileHandle = tmpfile();
        $tmpfilePath = stream_get_meta_data($tmpfileHandle)['uri'];


        // prendo l'immagine del ponte
        $isOpen = $task['is_open'];
        $status = $task['task_status'];

        $section = Section::find($task['section_id']);
        $bridgeMedia = $section->generic_images->last();

        if ($bridgeMedia) {

            $bridgeImagePath = $bridgeMedia->getPathBySize('');
            $bridgeImageInfo = getimagesize($bridgeImagePath);
            $image = imagecreate($bridgeImageInfo[0] * 2 , $bridgeImageInfo[1] * 2);
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

            imagecopy($image, $dest, $bridgeImageInfo[0] / 2, $bridgeImageInfo[1] / 2,  0, 0, $bridgeImageInfo[0], $bridgeImageInfo[1]);


            try {
                $pinPath = $this->getIcon($status, $isOpen);
                $iconInfo = getimagesize($pinPath);
                $src = imagecreatefrompng($pinPath);
                imagealphablending($src, false);
                imagesavealpha($src, true);
                // ridimensiono l'immagine del ponte e la fisso ad una larghezza fissa
                $sizeW =  5000;
                $sizeH =  $sizeW * ( $bridgeImageInfo[1] * 2 ) / ($bridgeImageInfo[0] * 2  ) ;

                $x = $bridgeImageInfo[0]/2 + $task['y_coord'] ;
                $y = ( $bridgeImageInfo[1] - $task['x_coord'] )  +  $bridgeImageInfo[1]/2;

                $xx = ($x * $sizeW ) / ($bridgeImageInfo[0]*2) ;
                $yy = ($y * $sizeH ) / ($bridgeImageInfo[1]*2) ;

                // imagepng($image, $map);
                imagepng($image, $mapfilePath);


                // $el = $this->resize_image($map, $sizeW, $sizeH);
                $el = $this->resize_image($mapfilePath, $sizeW, $sizeH);

                imagealphablending($el, false);
                imagesavealpha($el, true);

                fclose($mapfileHandle);
                imagecopymerge($el, $src, $xx- $iconInfo[0]/2, $yy - $iconInfo[1] , 0, 0, $iconInfo[0], $iconInfo[1], 100);


                imagealphablending($el, false);
                imagesavealpha($el, true);

                $crop_w = 728;
                $crop_h = 360;

                $im2 = imagecrop($el, ['x' => $xx - ($crop_w/2), 'y' => $yy - ($crop_h/2), 'width' => $crop_w, 'height' => $crop_h]);
                if ($im2 !== FALSE) {

                    imagealphablending($im2, false);
                    imagesavealpha($im2, true);
                    // imagepng($im2, $map);
                    imagepng($im2, $tmpfilePath);
                    imagedestroy($im2);
                }

                imagedestroy($dest);
                imagedestroy($src);
                imagedestroy($image);

                $this->addFileOrUpdateDocumentWithType($tmpfilePath, $this::CORROSION_MAP_DOCUMENT_TYPE, 'corrosion_map');
                fclose($tmpfileHandle); //this removes the tempfile

                return ['success' => true,  'H' => $sizeH, 'W' => $sizeW];

                //   imagealphablending($src, false);
                // imagesavealpha($src, true);
                // resize non funziona la trasparenza del pin
                //$iconInfo = [64, 96];
                //$src = $this->resize_image($pinPath, 64, 96);

                //       $sizeW =  $fixedSizeW;
                //   $sizeH =  $fixedSizeW * ( $bridgeImageInfo[1] ) / ($bridgeImageInfo[0] ) ;

                //     $cropY =  ( $sizeH - $task['x_coord'] + $iconInfo[1] ) +  $bridgeImageInfo[1];
                // $cropX = ( ( $task['y_coord'] - $sizeW / 2 ) ) +  $bridgeImageInfo[0];

                //imagealphablending($image, false);
                //  imagesavealpha($image, true);
                //$im2 = imagecrop($image, ['x' => $cropX, 'y' => $cropY, 'width' => $sizeW, 'height' => $sizeH]);
                //imagepng($im2, $path.DIRECTORY_SEPARATOR.'map1.png');
                //  imagealphablending($im2, false);
                //  imagesavealpha($im2, true);
                // imagecopymerge($im2, $src, $sizeW / 2 - ($iconInfo[0] / 2), $sizeH / 2 - ($iconInfo[1] ), 0, 0, $iconInfo[0], $iconInfo[1], 100);

                /*if ($im2 !== FALSE) {
                    imagepng($im2, $map);
                    imagedestroy($im2);
                }

                imagedestroy($dest);
                imagedestroy($src);
                imagedestroy($image);*/
                //  return ['success' => true, 'Y' => $cropY, 'X' => $cropX, 'H'=>$sizeH, 'W'=> $sizeW];
            } catch (\Exception $exc) {
                return ['success' => false, 'error' => $exc->getMessage()];
            }
        }
    }

    private function getIcon($status, $isOpen, $icon = 'Active')
    {
        /* return storage_path() .
          DIRECTORY_SEPARATOR . 'storm-pins'.DIRECTORY_SEPARATOR.'Active.png';
         */
        $icon = $icon . '.png';
        $status = str_replace(' ', '_', $status);
        $path = storage_path() . DIRECTORY_SEPARATOR . 'storm-pins';
        if (!$isOpen) {
            return $path . DIRECTORY_SEPARATOR . $status . DIRECTORY_SEPARATOR . $icon;
        }
        return $path . DIRECTORY_SEPARATOR . $status . DIRECTORY_SEPARATOR . $icon;
    }


    /**
     * Ridimensiona un'immagine da un path
     * @param type $file
     * @param type $w
     * @param type $h
     * @param type $crop
     * @return type
     */
    private function resize_image($file, $w, $h, $crop = FALSE)
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
     * @param $photos_array
     * @return string
     */
    public function getCorrosionMapHtml($photos_array)
    {
        $corrosionMapHTML = '';
        if ($corrosionMapFilePath = $this->getCorrosionMapFilePath()) {
            $corrosionMapHTML = <<<EOF
                <img width="760" height="auto" src="file://$corrosionMapFilePath" alt="Corrosion Map"><br>
EOF;
        }

        $point_id = $this->id;
        $task_location = $this->section ? Utils::sanitizeTextsForPlaceholders($this->section->name) : '?';
        $task_type = $this->intervent_type ? Utils::sanitizeTextsForPlaceholders($this->intervent_type->name) : '?';
        //         'task_status' => Utils::sanitizeTextsForPlaceholders($task->task_status),
        $description = Utils::sanitizeTextsForPlaceholders($this->description);
        $created_at = $this->created_at;
        $updated_at = $this->updated_at;

        $html = <<<EOF
            <p style="text-align: center;font-size: 21px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Point #$point_id</p>
                $corrosionMapHTML

                <table width="700" style="color: #1f519b; font-family: Raleway, sans-serif;">
                    <tr width="700">
                        <td width="345"><span style="border-right: 10px solid white; font-weight: bold">Location: </span>$task_location</td>
                        <td width="345"><span style="font-weight: bold">Type: </span>$task_type</td>
                    </tr>                    
                </table>

                <br>
                
                <table width="700" style="font-family: Raleway, sans-serif; color: #1f519b;">
                    <tr width="700">
                        <td width="345" rowspan="2" style="border-right: 10px solid white; vertical-align: top ;background-color: #eff9fe; padding: 8px">
                        <span style="font-weight: bold;">Description: </span>$description</td>
                        
                        <td width="345" rowspan="1" style="vertical-align: top;background-color: #eff9fe; padding: 8px">
                        <span style="font-weight: bold;">Created: </span>$created_at</td>
                    </tr>

                    <tr width="700">
                        <td width="345" rowspan="1" style="vertical-align: top;background-color: #eff9fe; padding: 8px">
                        <span style="font-weight: bold;">Last edited: </span>$updated_at</td>
                    </tr>
                </table>

                <br>

EOF;
        // creo la tabella a seconda delle immagini che ho
        if (!empty($photos_array) && count($photos_array) > 1) {
            $tds_1 = <<<EOF
                    <td width="345" align="center" style="border-right: 10px solid white; background-color: black; padding: 0;">
                      <img width="360" height="auto"  src="file://$photos_array[1]" alt="Corrosion img 1">
                    </td>
EOF;
            if (isset($photos_array[2])) {
                $tds_1 .= <<<EOF
                    <td width="345" align="center" style="background-color: black; padding: 0;">
                      <img width="360" height="auto"  src="file://$photos_array[2]" alt="Corrosion img 2">
                    </td>
EOF;
            }

            $trs = '<tr width="700">'.$tds_1.'</tr><br>';

            if (isset($photos_array[3])) {
                $tds_2 = <<<EOF
                    <td width="345" align="center" style="border-right: 10px solid white; background-color: black;padding: 0">
                      <img width="360" height="auto"  src="file://$photos_array[3]" alt="Corrosion img 3">
                    </td>
EOF;

                if (isset($photos_array[4])) {
                    $tds_2 .= <<<EOF
                        <td width="345" align="center" style="background-color: black;padding: 0;">
                          <img width="360" height="auto"  src="file://$photos_array[4]" alt="Corrosion img 4">
                        </td>
EOF;
                }

                $trs .= '<tr width="700">'.$tds_2.'</tr>';
            }

            $images_table =  '<p style="text-align: left;font-size: 16px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Detail photos</p>
                               <table width="792">'.$trs.'</table><br>';

            $html .= $images_table;
        }

        $img_dettaglioHTML = '';
        if ($img_dettaglio = $this->getAdditionalPhotoPath()) {
            $img_dettaglioHTML = <<<EOF
                <p style="text-align: left;font-size: 16px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Overview photo</p>
                <img width="760" height="auto" src="file://$img_dettaglio" alt="Overview image">
  
EOF;
        }

        $html .= <<<EOF
            $img_dettaglioHTML
            <p style="page-break-before: always;"></p>
EOF;
        return $html;
    }

}
