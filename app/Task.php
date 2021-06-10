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

use function date;
use function explode;
use function file_exists;
use function in_array;
use function is_object;
use function strtotime;
use const DIRECTORY_SEPARATOR;
use const PROJECT_STATUS_CLOSED;
use const TASK_TYPE_PRIMARY;
use const TASK_TYPE_REMARK;
use const TASKS_STATUS_COMPLETED;
use const TASKS_STATUS_DENIED;
use const TASKS_STATUS_R_CLOSED;
use const TASKS_STATUSES;

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
        'internal_progressive_number',
        'zone_id',
        'task_type',
    ];
    private $min_x;
    private $max_x;
    private $min_y;
    private $max_y;

    public $last_history;

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
     * Scope a query to only include only remarks
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRemark($query)
    {
        return $query->where('task_type', '=', TASK_TYPE_REMARK);
    }

    /**
     * Scope a query to only include only primary
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimary($query)
    {
        return $query->where('task_type', '=', TASK_TYPE_PRIMARY);
    }

    /**
     * Scope a query to only include only open Tasks
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpened($query)
    {
        return $query->where('is_open', '=', 1);
    }

    /**
     * Scope a query to only include only open Tasks
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->where('is_open', '=', 0);
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

    /**
     * @return History|object|null
     */
    public function getLastHistory()
    {
        return $this->last_history ?? $this->history()->latest()->first();
    }

    /**
     * @return array|null
     */
    public function getLastHistoryForApi()
    {
        $last_history = $this->getLastHistory();
        if ($last_history) {
            $data = [
                'id' => $last_history->id,
                'type' => History::class,
                'attributes' => $last_history
            ];
            Arr::forget($data['attributes'], 'documents');
            $data['attributes']['comments'] = $last_history->comments_for_api;
            $data['attributes']['photos'] = $last_history->getPhotosApi('data', 'thumb');
            return $data;
        }
        return null;
    }

    /**
     * @return History|object|null
     */
    public function setLastHistory()
    {
        $this->last_history = $this->history()->latest()->first();
    }

    /**
     * @return History|object|null
     */
    public function getFirstHistory()
    {
        return $this->history()->oldest()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function taskIntervents()
    {
        return $this->hasOne('App\TaskInterventType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone()
    {
        return $this->belongsTo('App\Zone', 'zone_id');
    }

    /**
     * ->zone_text
     * @return string|null
     */
    public function getZoneTextAttribute()
    {
        return $this->zone ? $this->zone->code.' - '.$this->zone->description : null;
    }


    /**
     * Application Log from where the Task has been closed
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function closer_application_log()
    {
        return $this->belongsToMany('App\ApplicationLog', 'App\ApplicationLogTask')->wherePivot('action', '=', 'close');
    }

    /**
     * Application Log from where the Task has been opened
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function opener_application_log()
    {
        return $this->belongsToMany('App\ApplicationLog', 'App\ApplicationLogTask')->wherePivot('action', '=', 'open');
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
     * @param $project_id
     * @return \Illuminate\Support\Collection
     */
    public static function getAllAuthors($project_id) {

        $user = \Auth::user();
        $q = User::join('tasks', 'users.id', '=', 'tasks.author_id')
            ->where('tasks.project_id', '=', $project_id);
        if ($user && !$user->is_storm) {
            $q = $q->where('tasks.is_private', '!=', 1);
        }
        return $q->select('users.id', 'users.name', 'users.surname')
            ->orderBy('users.name', 'asc')
            ->distinct()
            ->get();
    }

    public static function getSemiFakeData(Faker $faker, Project $proj = null, Section $sect = null, Subsection $ssect = null, User $author = null, TaskInterventType $type = null)
    {
        $status = $faker->randomElement(TASKS_STATUSES);
        $is_open = is_object($proj) ? ($proj->project_status != PROJECT_STATUS_CLOSED) : !in_array($status, [TASKS_STATUS_COMPLETED, TASKS_STATUS_DENIED]);

        return [
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
        ];
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
     * @return Task $t
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     *
     */
    public static function createSemiFake(Faker $faker, Project $proj = null, Section $sect = null, Subsection $ssect = null, User $author = null, TaskInterventType $type = null)
    {
        $status = $faker->randomElement(TASKS_STATUSES);
        $t = new Task(self::getSemiFakeData($faker, $proj, $sect, $ssect, $author, $type));
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
     * @param string $filepath
     * @param string|null $type
     * @return Document
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
     * Retrieve additional image's path
     *
     * @return string
     */
    public function getAdditionalPhotoPath()
    {
        return $this->getDocumentMediaFilePath(Document::ADDITIONAL_IMAGE_TYPE, 'report-large');
    }

    /**
     * Retrieve detailed image's path
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
        $document = $this->documents->where('type', self::CORROSION_MAP_DOCUMENT_TYPE)->last();
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


        $mapfileHandle = tmpfile();
        $mapfilePath = stream_get_meta_data($mapfileHandle)['uri'];


        $tmpfileHandle = tmpfile();
        $tmpfilePath = stream_get_meta_data($tmpfileHandle)['uri'];


        // prendo l'immagine del ponte
        $isOpen = $task['is_open'];
        $status = $task['task_status'];

//        $section = Section::find($task['section_id']);
        $section = $task->section;
        if ($section) {
            $bridgeMedia = $section->generic_images->last();
            if ($bridgeMedia) {

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
                    $pinPath = $this->getIcon($status, $isOpen);
                    $iconInfo = getimagesize($pinPath);
                    $src = imagecreatefrompng($pinPath);
                    imagealphablending($src, false);
                    imagesavealpha($src, true);
                    // ridimensiono l'immagine del ponte e la fisso ad una larghezza fissa
                    $sizeW = 5000;
                    $sizeH = $sizeW * ($bridgeImageInfo[1] * 2) / ($bridgeImageInfo[0] * 2);

                    $x = $bridgeImageInfo[0] / 2 + $task['y_coord'];
                    $y = ($bridgeImageInfo[1] - $task['x_coord']) + $bridgeImageInfo[1] / 2;

                    $xx = ($x * $sizeW) / ($bridgeImageInfo[0] * 2);
                    $yy = ($y * $sizeH) / ($bridgeImageInfo[1] * 2);

                    // imagepng($image, $map);
                    imagepng($image, $mapfilePath);


                    // $el = $this->resize_image($map, $sizeW, $sizeH);
                    $el = $this->resize_image($mapfilePath, $sizeW, $sizeH);

                    imagealphablending($el, false);
                    imagesavealpha($el, true);

                    fclose($mapfileHandle);
                    imagecopymerge($el, $src, $xx - $iconInfo[0] / 2, $yy - $iconInfo[1], 0, 0, $iconInfo[0], $iconInfo[1], 100);


                    imagealphablending($el, false);
                    imagesavealpha($el, true);

                    $crop_w = 728;
                    $crop_h = 360;

                    $im2 = imagecrop($el, ['x' => $xx - ($crop_w / 2), 'y' => $yy - ($crop_h / 2), 'width' => $crop_w, 'height' => $crop_h]);
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

                    return ['success' => true, 'H' => $sizeH, 'W' => $sizeW];

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
    }

    /**
     * @param null $p_status
     * @param null $p_isOpen
     * @param string $icon
     * @param bool $miniature
     * @return string
     */
    public function getIcon($p_status = null, $p_isOpen = null, $icon = 'Active', $miniature = false)
    {
        $status = $p_status ?? $this->task_status;
        $isOpen = $p_isOpen ?? $this->is_open;
//        $ext = in_array($status, ['monitored', 'completed', 'denied']) ? '.svg' : '.png';
        $ext = '.png';
        $icon = $icon . $ext;
        $status = str_replace(' ', '_', $status);
        $dir = $miniature ? 'storm-pins-half' : 'storm-pins';
        $path = storage_path() . DIRECTORY_SEPARATOR . $dir;
        if (!$isOpen && file_exists($path . DIRECTORY_SEPARATOR . $status . DIRECTORY_SEPARATOR . 'closed' . DIRECTORY_SEPARATOR . $icon)) {
            return $path . DIRECTORY_SEPARATOR . $status . DIRECTORY_SEPARATOR . 'closed' . DIRECTORY_SEPARATOR . $icon;
        }
        return $path . DIRECTORY_SEPARATOR . $status . DIRECTORY_SEPARATOR . $icon;
    }


    /**
     * FIXME: non ha senso questa funzione privata e non statica. Va fatta statica e messa fuori da un Model specifico. ZIOBE'
     *
     * Ridimensiona un'immagine da un path
     * @param $file
     * @param $w
     * @param $h
     * @param $crop
     * @return mixed
     */
    private function resize_image($file, $w, $h, $crop = FALSE)
    {
        return StormUtils::resize_image($file, $w, $h, $crop); // vedi il FIXME sopra
    }

    /**
     * @param $photos_array
     * @return string
     */
    public function getCorrosionMapHtml($photos_array)
    {
        $point_id = $this->internal_progressive_number;
        $task_location = $this->section ? Utils::sanitizeTextsForPlaceholders($this->section->name) : '?';
        $task_intervent_type = $this->intervent_type ? Utils::sanitizeTextsForPlaceholders($this->intervent_type->name) : '?';
        //         'task_status' => Utils::sanitizeTextsForPlaceholders($task->task_status),
        $created_at = date('d M Y', strtotime($this->created_at));
        $updated_at = date('d M Y', strtotime($this->updated_at));
        $status = $this->task_status;
        $task_type = $this->task_type;

        $first_history = $this->getFirstHistory();
        $overviewImgOnPoint = '<span style="color: #666666; width: 100%;">Overview photo not available</span>';
        $hasOverviewImgOnPoint = false;
        if ($first_history && $first_history->getAdditionalPhotoPath()) {
            $hasOverviewImgOnPoint = true;
            $img_dettaglio = $first_history->getAdditionalPhotoPath();
            $overviewImgOnPoint = <<<EOF
		<img src="file://$img_dettaglio" alt="Overview image"
                     width="442"
                     style="max-height: 370px;
                            float: left;" />
EOF;
        }

        $taskDescription = '';
        $description = Utils::sanitizeTextsForPlaceholders($this->description);
        if ($description) {
            $taskDescription = "<span style='color: #666666; width: 100%; padding: 8px'>$description</span>";
        }

        if ($hasOverviewImgOnPoint) {
            $overviewImgTable = <<<EOT
                <table >
                    <tbody>
                        <tr>
                            <td width="310">
                                $overviewImgOnPoint
                            </td>
                            <td width="30"></td>
                            <td width="340" valign="top">
                                $taskDescription
                            </td>
                        </tr>
                    </tbody>
                </table>
EOT;
        } else {
            $overviewImgTable = <<<EOT
                <table>
                    <tbody>
                       <tr>
                            <td width="310" valign="top">
                                $overviewImgOnPoint
                            </td>
                            <td width="30"></td>
                            <td width="350" valign="top">
                                $taskDescription
                            </td>
                        </tr>
                    </tbody>
                </table>
EOT;
        }

        // location (sundec), type (damage), status (in progress)
        if ($task_type == TASK_TYPE_PRIMARY) {
            $pointInfo = <<<EOF
                    <span>
                        <p>Location: <b>$task_location</b></p> <br />
                        <p>Type: <b>$task_intervent_type</b></p> <br />
                        <p>Status: <b>$status</b></p>
                    <span>
EOF;
        } else {
            $remarkedDate = $this->created_at->format('d-m-Y');
            $pointInfo = <<<EOF
                    <span>
                        <p>Location: <b>$task_location</b></p> <br />
                        <p>Type: <b>Remark</b></p> <br />
                        <p>Status: <b>$status</b></p> <br />
                        <p>Created at: <b>$remarkedDate</b></p> <br />
                    <span>
EOF;
        }

        $corrosionMapHTML = '';
        if ($corrosionMapFilePath = $this->getCorrosionMapFilePath()) {
            $corrosionMapHTML = <<<EOF
                <img src="file://$corrosionMapFilePath" alt="Overview image"
                     width="442"
                     style="max-height: 370px;
                            float: left;" />
EOF;
        }

        $html = <<<EOF
            <h3 style="text-align: center;font-size: 19px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Point #$point_id</h3>
            <table cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td width="350" valign="top">
                            <span style="font-weight: bold; color: #1f519b;">Position</span>
                        </td>
                        <td width="30"></td>
                        <td width="350" valign="top">
                            <span style="font-weight: bold; color: #1f519b;">Point info</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="310" style="border: 1px solid #ececec">
                            $corrosionMapHTML
                        </td>
                        <td width="30"></td>
                        <td width="340" valign="top">
                            $pointInfo
                        </td>
                    </tr>

                    <tr height="30"></tr>

                    <tr>
                        <td width="350" valign="top">
<!--                        <td colspan="2" valign="top">-->
                            <span style="color: #1f519b; font-weight: bold">Overview</span><br>
                        </td>
                        <td width="30"></td>
                        <td width="350" valign="top">
<!--                        <td colspan="2" valign="top" style="margin-left: 60px;">-->
                            <span style="color: #1f519b; font-weight: bold;">Description</span><br>
                        </td>
                    </tr>
                </tbody>
            </table>

            $overviewImgTable
EOF;

        // creo la tabella a seconda delle immagini che ho
        if (!empty($photos_array) && count($photos_array) > 1) {
            $tds_1 = <<<EOF
                    <td width="174">
                        <img width="174" src="file://$photos_array[1]" alt="Corrosion img 1">
                    </td>
EOF;

            if (isset($photos_array[2])) {
                $tds_1 .= <<<EOF
                    <td width="174">
                        <img width="174" src="file://$photos_array[2]" alt="Corrosion img 2">
                    </td>
EOF;
            }

            $trs = '<tr>' . $tds_1 . '</tr><tr height=30></tr>';

            if (isset($photos_array[3])) {
                $tds_2 = <<<EOF
                    <td width="174">
                        <img width="174" src="file://$photos_array[3]" alt="Corrosion img 3">
                    </td>
EOF;

                if (isset($photos_array[4])) {
                    $tds_2 .= <<<EOF
                        <td width="174">
                            <img width="174"  src="file://$photos_array[4]" alt="Corrosion img 4">
                        </td>
EOF;
                }

                $trs = '<tr>' . $tds_1 . $tds_2 . '</tr>';
            }

            $theadContent = '<p style="text-align: left;font-size: 15px;font-weight: bold; font-family: Raleway, sans-serif; color: #1f519b;">Detail photos</p>';
            $images_table = <<<EOF
                            <table style="float: right"><thead>$theadContent</thead><tbody>$trs</tbody></table>
EOF;
            $html .= $images_table;
        } else {
            $html .= '<br><br><span style="text-align: left;font-size: 16px;font-weight: bold; font-family: Raleway, sans-serif; color: #1f519b;">Detail photos</span><br><span style="color: #666666">Photos not available</span>';
        }

        $html .= <<<EOF

            <p style="page-break-before: always;"></p>
EOF;
        return $html;
    }

    /**
     * An internal ID calculated on a "per-boat" base
     * @param $boat_id
     * @return integer
     */
    public static function getLastInternalProgressiveIDByBoat($boat_id)
    {
        $max = Task::join('projects', 'projects.id', '=', 'tasks.project_id')
            ->where('projects.boat_id', '=', $boat_id)
            ->max('tasks.internal_progressive_number');
        return $max ? $max : 0;
    }

    /**
     * Goives total number of tasks calculated on a "per-boat" base
     * @param $boat_id
     * @return integer
     */
    public static function countTasksByBoat($boat_id)
    {
        return Task::join('projects', 'projects.id', '=', 'tasks.project_id')
            ->where('projects.boat_id', '=', $boat_id)
            ->withTrashed()
            ->count();
    }

    /**
     * @return void
     */
    public function updateInternalProgressiveNumber()
    {
        if (env('INTERNAL_PROG_NUM_ACTIVE')) {
            $p_boat = $this->getProjectBoat();
            if ($p_boat) {
                $highest_internal_pn = Task::getLastInternalProgressiveIDByBoat($p_boat->id);
                $this->update(['internal_progressive_number' => ++$highest_internal_pn]);
            }
        }
    }

    /**
     * Get user who did last edit
     *
     * @return string|null
     */
    public function getLastEditor()
    {
        /** @var History $last_history */
        $last_history = $this->getLastHistory();
        if ($last_history) {
            return $last_history->getBodyAttribute('user_name');
        }
    }

    /**
     * Get user who did last edit
     *
     * @return string|null
     */
    public function getLastEditorId()
    {
        /** @var History $last_history */
        $last_history = $this->getLastHistory();
        if ($last_history) {
            return $last_history->getBodyAttribute('user_id');
        }
    }

    /**
     * This function "closes" the Task, setting some fields
     *
     * @param ApplicationLog $application_log
     */
    public function closeMe(ApplicationLog $application_log = null)
    {
        $this->update([
            'is_open' => false,
//            'task_status' => $this->task_type == TASK_TYPE_PRIMARY ? TASKS_STATUS_COMPLETED : TASKS_STATUS_R_CLOSED
        ]);

        if ($application_log) {
            $application_log->closeTask($this);
        }
    }

    /**
     * This function "open" the Task, setting some fields
     *
     * @param ApplicationLog $application_log
     * @param null $status
     */
    public function openMe(ApplicationLog $application_log = null, $status = null)
    {
        $this->update([
            'is_open' => true,
            'task_status' => $status
        ]);

        if ($application_log) {
            $application_log->openTask($this);
        }
    }
}
