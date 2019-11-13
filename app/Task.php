<?php

namespace App;

use function explode;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Observers\TaskObserver;
use Spatie\ModelStatus\HasStatuses;
use Venturecraft\Revisionable\RevisionableTrait;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use Faker\Generator as Faker;
use App\Section;
use function in_array;
use function is_object;
use const PROJECT_STATUS_CLOSED;
use const TASKS_STATUS_COMPLETED;
use const TASKS_STATUS_DENIED;

class Task extends Model
{
    use RevisionableTrait, HasStatuses, DocumentableTrait;

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
        'bridge_position',

    ];

    private $min_x;
    private $max_x;
    private $min_y;
    private $max_y;

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


    protected static function boot()
    {
        parent::boot();

        Task::observe(TaskObserver::class);
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
     *
     * @return Task $t
     */
    public static function createSemiFake(Faker $faker,
                                          Project $proj = null,
                                          Section $sect = null,
                                          Subsection $ssect = null,
                                          User $author = null,
                                          TaskInterventType $type = null)
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
                'y_coord' => $faker->randomFloat(2, 267.95, 307.95),  // scostarsi del 5% dal punto  287.95
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
            'y_coord' => $faker->randomFloat(2, $this->min_y ? $this->min_y : 267.95, $this->max_y ? $this->max_y : 307.95),  // scostarsi del 5% dal punto  287.95
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

    public function generateBridgePositionFileFromBase64(){
        $base64 = $this->bridge_position;
        $handle = tmpfile();
        $path = stream_get_meta_data($handle)['uri'];
        $data = explode(',', $base64);

        fwrite($handle, base64_decode($data[1]));
        fseek($handle, 0);


        $pngPath = $path . ".jpg";

        shell_exec("convert " . $path  . " "  . $pngPath);

        return [
            'path' => $pngPath,
            'handle' => $handle
        ];

    }

    public function removeTempFileByHandle($handle){
        fclose ($handle); // this removes the file
    }
    
    
    public function updateMap() {
        $task =$this;
        $map = storage_path() . DIRECTORY_SEPARATOR . 'map.png';
        // prendo l'immagine del ponte 
        $isOpen = $task['is_open'];
        $status = $task['task_status'];

        $section = Section::find($task['section_id']);
        $bridgeMedia = $section->generic_images->last();

        $bridgeImagePath = $bridgeMedia->getPathBySize('');
        $bridgeImageInfo = getimagesize($bridgeImagePath);
        $image = imagecreate ($bridgeImageInfo[0] ,$bridgeImageInfo[1]  ) ;
                 imagecolorallocate (  $image ,255,255 , 255 );

        // sfondo bianco
       // $im = @imagecreate(110, 20)  or die("Cannot Initialize new GD image stream");
       // $background_color = imagecolorallocate($im, 255, 255, 255);
        
        if (exif_imagetype($bridgeImagePath) === IMAGETYPE_PNG) {
            // il ponte e' un'immagine png
            $dest = imagecreatefrompng($bridgeImagePath);
        }

        if (exif_imagetype($bridgeImagePath) === IMAGETYPE_JPEG) {
            // il ponte e' un'immagine jpg
            $dest = imagecreatefromjpeg($bridgeImagePath);
        }
        imagecopy($image, $dest, 0, 0, 0, 0, $bridgeImageInfo[0] ,$bridgeImageInfo[1]);
        try {
            
            $pinPath = $this->getIcon($status, $isOpen);
            $iconInfo = getimagesize($pinPath);
            $src = imagecreatefrompng($pinPath);
            //  imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
            imagecopymerge($image, $src, $task['y_coord' ] - $iconInfo[0]/2, $bridgeImageInfo[1] - $task['x_coord'] - $iconInfo[1], 0, 0, $iconInfo[0], $iconInfo[1], 75);
            $sizeW = $bridgeImageInfo[0]/2;
            $sizeH = $bridgeImageInfo[1]/2;
            $im2 = imagecrop($image, ['x' => ( $task['y_coord'] - $sizeW /2 ) + ( $iconInfo[0] /2 ) , 'y' =>   $sizeH+ $task['x_coord']  - $iconInfo[1] , 'width' => $sizeW, 'height' => $sizeH]);
            if ($im2 !== FALSE) {
                imagepng($im2, $map);
                imagedestroy($im2);
            } 
            imagedestroy($dest);
            imagedestroy($src); 
            imagedestroy($image); 
            $mapfile = file_get_contents($map);
            $this->bridge_position = "data:image/png;base64,".base64_encode($mapfile);
            $this->save();
         //   unlink($map);
            return ['success' => true] ;
            
        } catch (\Exception $exc) {
            return ['success' => false, 'error' =>$exc->getMessage() ] ;
        }
        
    }

    private function getIcon($status, $isOpen, $icon = 'Active') {
        $icon = $icon.'.png';
        $path = storage_path() . DIRECTORY_SEPARATOR . 'storm-pins';
        if (!$isOpen) {
            return $path.DIRECTORY_SEPARATOR.$status.DIRECTORY_SEPARATOR.$icon;
        } 
       return $path.DIRECTORY_SEPARATOR.$status.DIRECTORY_SEPARATOR.$icon;
    }
    /*
    static getIconPath(state, isOpen) {
     const Icons = {
      draft: '/assets/storm-pins/Draft/Active.svg',
      submitted: '/assets/storm-pins/Sent/Active.svg',
      accepted: '/assets/storm-pins/Accepted/Active.svg',
      'closed-completed': '/assets/storm-pins/Closed/Completed/Active.svg',
      'closed-denied': '/assets/storm-pins/Closed/Declined/Active.svg',
      completed: '/assets/storm-pins/Completed/Active.svg',
      denied: '/assets/storm-pins/Declined/Active.svg',
      'in progress': '/assets/storm-pins/Progress/Active.svg',
      monitored: '/assets/storm-pins/Monitored/Active.svg',
      remarked: '/assets/storm-pins/Remark/Active.svg',
    };
     if (!isOpen && state === 'completed') {
        return Icons['closed-completed'];
      }
     if (!isOpen && state === 'denied') {
        return Icons['closed-denied'];
    }
     return Icons[state];
  }
    */
    
}
