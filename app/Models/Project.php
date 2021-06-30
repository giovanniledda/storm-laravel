<?php

namespace App\Models;

use App\Jobs\SendDocumentsToGoogleDrive;
use App\Observers\ProjectObserver;
use App\Traits\EnvParamsInputOutputTranslations;
use App\Traits\TemplateReplacementRules;
use function App\Utils\createCsvFileFromHeadersAndRecords;
use function App\Utils\sanitizeTextsForPlaceholders;
use function array_key_exists;
use function array_map;
use function collect;
use function date;
use function explode;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function implode;
use function json_decode;
use League\Csv\Writer;
use const MEASUREMENT_FILE_TYPE;
use Net7\DocsGenerator\Traits\HasDocsGenerator;
use Net7\DocsGenerator\Utils;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use Net7\EnvironmentalMeasurement\Traits\HasMeasurements;
use function preg_replace;
use const PROJECT_STATUS_CLOSED;
use function request;
use Spatie\ModelStatus\HasStatuses;
use function strtotime;
use const TASK_TYPE_PRIMARY;
use const TASKS_STATUS_ACCEPTED;
use const TASKS_STATUS_DRAFT;
use const TASKS_STATUS_MONITORED;

// use Illuminate\Support\Facades\Queue;

class Project extends Model
{
    use HasFactory;
    use DocumentableTrait {
        addDocumentWithType as traitAddDocumentWithType;
        updateDocument as traitUpdateDocument;
        deleteDocument as traitDeleteDocument;
    }

    use HasDocsGenerator {
        addTemplateResultDocument as traitAddTemplateResultDocument;
        getTemplateResultDocument as traitGetTemplateResultDocument;
    }

    use HasStatuses,
        SerializesModels,
        HasMeasurements,
        TemplateReplacementRules,
        EnvParamsInputOutputTranslations;

    protected $table = 'projects';
    protected $fillable = [
        'name',
        'project_status',
        'boat_id',
        'project_type',
        'project_progress',
        'site_id',
        'start_date',
        'end_date',
        'imported',
        'internal_progressive_number',
    ];

    public const REPORT_FOLDER = 'reports';
    public const DOCUMENTS_FOLDER = 'documents';
    public const REPORT_DOCUMENT_TYPE = 'report';

    protected static function boot()
    {
        parent::boot();

        self::observe(ProjectObserver::class);
    }

    public function deleteDocument(Document $document)
    {
        $this->deleteFromCloud($document);

        return $this->traitDeleteDocument($document);
    }

    private function deleteFromCloud(Document $document)
    {
        if ($this->shouldUseCloud($document)) {
            if (env('USE_DROPBOX')) {
                //TODO
            }

            if (env('USE_GOOGLE_DRIVE')) {

                // TODO: delete document from google

                $this->deleteDocumentFromGoogleDrive($document);

                // SendDocumentsToGoogleDrive::dispatch($this, $document);
                // $this->sendDocumentToGoogleDrive($document);
            }
        }
    }

    public function addTemplateResultDocument($temporary_final_file_path, $final_file_name, $template_object_id, $type = self::REPORT_DOCUMENT_TYPE, $subtype = '')
    {

        // $document = $this->traitAddTemplateResultDocument($temporary_final_file_path, $final_file_name, $template_object_id, $type);

        if (! $type) {
            $type = self::REPORT_DOCUMENT_TYPE;
        }

        // $this->addDocumentFile will call the $this->addDocumentWithType method, which in turn will
        //  take care of google and/or dropbox sync
        $document = $this->addDocumentFile($temporary_final_file_path, $final_file_name, $type, $subtype);
        unlink($temporary_final_file_path);

        return $document;
    }

    public function getTemplateResultDocument($template_object_id)
    {
        return $this->traitGetTemplateResultDocument($template_object_id);
    }

    public function sendDocumentToDropbox(Document $document)
    {
        $document = Document::find($document->id);
        $media = $document->getRelatedMedia();

        $filepath = $media->getPath();

        $fh = fopen($filepath, 'r');
        $content = fread($fh, filesize($filepath));
        fclose($fh);

        $filename = $media->file_name;
        $dropboxFolder = $this->getDropboxFolderPath($document);
        $dropboxFilepath = $this->getDropboxFilePath($document, $filename);

        $client = new \Spatie\Dropbox\Client(env('DROPBOX_TOKEN'));
        try {
            $client->listFolder($dropboxFolder);
        } catch (\Spatie\Dropbox\Exceptions\BadRequest  $e) {
            $client->createFolder($dropboxFolder);
        }

        $client->upload($dropboxFilepath, $content, 'add');

        // $client->getMetadata($fullPath);

        // TODO: remove local file

        // TODO: check for errors

        // TODO: finish it up
    }

    public function getGooglePathFromHumanPath($path)
    {
        $lastPath = '';
        $contents = collect(Storage::cloud()->listContents($lastPath, false));

        $lastDir = false;
        $folderSteps = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($folderSteps as $step) {

            // apparently we can't just ask google drive for subdirectories, like
            // '/boats/Pinta/3'
            // we need to cycle dir by dir and use their IDs to identify the directories

            if (! trim($step)) {
                // sometimes there are empty steps, go figure
                continue;
            }

            $dir = $contents->where('type', '=', 'dir')->where('filename', '=', $step)->first();

            if (! $dir) {
                // we need to create it
                $path = '';
                if ($lastDir) {
                    $path .= $lastPath.'/';
                }

                $path .= $step;
                Storage::cloud()->makeDirectory($path);
                // we refresh the value so we can take the new dir ['path'] value
                $contents = collect(Storage::cloud()->listContents($lastPath, false));
            }

            $lastDir = $contents->where('type', '=', 'dir')->where('filename', '=', $step)->first();
            $lastPath = $lastDir['path'];
            $contents = collect(Storage::cloud()->listContents($lastPath, false));
        }

        $path = $lastDir['path'];

        return $path;
    }

    public function deleteDocumentFromGoogleDrive(Document $document)
    {
        $media = $document->getRelatedMedia();
        $filename = $media->file_name;

        $cloudStorageData = json_decode($document->cloud_storage_data, true);

        if (isset($cloudStorageData['path'])) {
            $googleFolder = $cloudStorageData['path'];
        } else {
            $googleFolder = $this->getGoogleFolderPath($document);
        }
        if (isset($cloudStorageData['filename'])) {
            $filename = $cloudStorageData['filename'];
        } else {
            $filename = $this->getGoogleFilename($document, $filename);
        }

        $path = $this->getGooglePathFromHumanPath($googleFolder);

        $recursive = false; // Get subdirectories also?
        $contents = collect(Storage::cloud()->listContents($path, $recursive));

        // Get file details...
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        // $readStream = Storage::cloud()->getDriver()->readStream($file['path']);

        $resp = Storage::cloud()->delete($file['path']);

        return $resp;
        //'File was deleted from Google Drive';
    }

    public function sendDocumentToGoogleDrive(Document $document)
    {
        $document = Document::find($document->id);
        $media = $document->getRelatedMedia();

        $filepath = $media->getPath();

        $fh = fopen($filepath, 'r');
        $content = fread($fh, filesize($filepath));
        fclose($fh);

        $filename = $media->file_name;
        $googleFolder = $this->getGoogleFolderPath($document);
        $filename = $this->getGoogleFilename($document, $filename);

        $path = $this->getGooglePathFromHumanPath($googleFolder);

        // now we have the full path made of directory Ids, we can upload our file there.
        $path .= '/'.$filename;
        Storage::cloud()->put($path, $content);

        //TODO: check errors?

        $link = $this->getDocumentLinkFromGoogle($document);
        $document_cloud_storage_data = json_decode($document['cloud_storage_data'], true);

        $document_cloud_storage_data['gdrive_link'] = $link;
        $document_cloud_storage_data['gdrive_filename'] = $filename;
        $document['cloud_storage_data'] = json_encode($document_cloud_storage_data);
        $document->save();
    }

    /**
     * @Override the base method to send the updated files to dropbox
     */
    public function updateDocument(Document $document, $file, $useCloud = true)
    {
        $this->traitUpdateDocument($document, $file);
        $this->save();
        $document->refresh();
        // if ($document->type != MEASUREMENT_FILE_TYPE) {
        if ($this->shouldUseCloud($document)) {
            if ($useCloud) {
                if (env('USE_DROPBOX')) {
                    $this->sendDocumentToDropbox($document);
                }

                if (env('USE_GOOGLE_DRIVE')) {
                    SendDocumentsToGoogleDrive::dispatch($this, $document);
                    // $this->sendDocumentToGoogleDrive($document);
                }
            }
        }

        return $document;
    }

    /**
     * @Override the base method to send files to dropbox
     */
    public function addDocumentWithType(Document $document, $type, $useCloud = true)
    {
        $this->traitAddDocumentWithType($document, $type);

        $this->save();
        $document->refresh();

        // if ($type != MEASUREMENT_FILE_TYPE) {
        if ($this->shouldUseCloud($document)) {
            if ($useCloud) {
                if (env('USE_DROPBOX')) {
                    $this->sendDocumentToDropbox($document);
                }

                if (env('USE_GOOGLE_DRIVE')) {
                    SendDocumentsToGoogleDrive::dispatch($this, $document);
                    // $this->sendDocumentToGoogleDrive($document);
                }
            }
        }

        return $document;
    }

    private function shouldUseCloud(Document $document)
    {
        if ($document->type == MEASUREMENT_FILE_TYPE) {
            return false;
        }

        return true;
    }

    public function getDocumentFromDropbox(Document $document)
    {
        $media = $document->getRelatedMedia();
        $filename = $media->file_name;
        $dropboxFolder = $this->getDropboxFolderPath($document);
        $dropboxFilepath = $this->getDropboxFilePath($document, $filename);
        $client = new \Spatie\Dropbox\Client(env('DROPBOX_TOKEN'));
        $link = $client->getTemporaryLink($dropboxFilepath);

        return $link;
    }

    public function getDocumentLinkFromGoogle(Document $document)
    {
        return $this->getDocumentFromGoogle($document, true);
    }

    public function getDocumentFromGoogle(Document $document, $justALink = false)
    {
        $media = $document->getRelatedMedia();
        $filename = $media->file_name;

        $cloudStorageData = json_decode($document->cloud_storage_data, true);

        if (isset($cloudStorageData['path'])) {
            $googleFolder = $cloudStorageData['path'];
        } else {
            $googleFolder = $this->getGoogleFolderPath($document);
        }
        if (isset($cloudStorageData['filename'])) {
            $filename = $cloudStorageData['filename'];
        } else {
            $filename = $this->getGoogleFilename($document, $filename);
        }

        $path = $this->getGooglePathFromHumanPath($googleFolder);

        $recursive = false; // Get subdirectories also?
        $contents = collect(Storage::cloud()->listContents($path, $recursive));

        // Get file details...
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        if ($justALink) {
            $service = Storage::cloud()->getAdapter()->getService();
            $permission = new \Google_Service_Drive_Permission();
            $permission->setRole('reader');
            $permission->setType('anyone');
            $permission->setAllowFileDiscovery(false);
            $permissions = $service->permissions->create($file['basename'], $permission);

            // I couldn't find a method to create this, I guess it's alright doing it this way...
            $link = 'https://docs.google.com/document/d/'.$file['basename'].'/edit';

            return $link;

            // if we want the downloadable file link
            return Storage::cloud()->url($file['path']);
        }

        $readStream = Storage::cloud()->getDriver()->readStream($file['path']);

        return response()->stream(function () use ($readStream) {
            fpassthru($readStream);
        }, 200, [
            'Content-Type' => $media->mime_type,
            'Content-disposition' => 'attachment; filename="'.$filename.'"', // force download?
        ]);

        /*
         $readStream = Storage::cloud()->getDriver()->readStream($file['path']);

            return response()->stream(function () use ($readStream) {
                fpassthru($readStream);
            }, 200, [
                'Content-Type' => $file['mimetype'],
                //'Content-disposition' => 'attachment; filename="'.$filename.'"', // force download?
            ]);
                return $link;
        */
    }

    public function getRelatedMedia()
    {
        // return $this->media;
    }

    public function getDropboxFilename($document, $filename)
    {
        $media = $document->getRelatedMedia();

        $path_parts = pathinfo($this->getMediaPath($media).$filename);

        $basename = $path_parts['filename'];
        $extension = $path_parts['extension'];

        if ($document->type == self::REPORT_DOCUMENT_TYPE) {
            $basename .= '__'.date('Y_m_d__h_i', time());
        }

        return $basename.'__'.$media->id.'.'.$extension;
    }

    public function getDropboxFilePath($document, $filename)
    {
        $path = $this->getDropboxFolderPath($document).$this->getDropboxFilename($document, $filename);

        return $this->sanitizePathForCloudStorages($path);
    }

    public function getDropboxFolderPath($document = false)
    {
        $boat = $this->boat;
        $project_id = $this->id;
        $boat_name = $boat->name;

        $path = '';
        if (env('CLOUD_BASE_DIR')) {
            $path .= DIRECTORY_SEPARATOR.env('CLOUD_BASE_DIR');
        }

        $path .= DIRECTORY_SEPARATOR.'boats'.DIRECTORY_SEPARATOR.$boat_name.'_'.sprintf('%07d', $project_id).'_'.
            $this->project_type.'_'.date('Y-m-d', strtotime($this->created_at)).DIRECTORY_SEPARATOR;

        if ($document && $document->document_number) {
            $path .= $document->document_number.DIRECTORY_SEPARATOR;
        }

        if ($document && $document->type == self::REPORT_DOCUMENT_TYPE) {
            $path .= $this::REPORT_FOLDER.DIRECTORY_SEPARATOR;
        }

        return $this->sanitizePathForCloudStorages($path);
    }

    public function getGoogleFilename($document, $filename)
    {
        return $this->getDropboxFilename($document, $filename);
    }

    // public function getGoogleFilePath ($document, $filename){
    //     return $this->getDropboxFilePath ($document, $filename);
    // }

    public function getGoogleFolderPath($document)
    {
        return $this->getDropboxFolderPath($document);
    }

    public function getGoogleProjectReportsFolderPath()
    {
        return $this->getDropboxFolderPath().$this::REPORT_FOLDER.DIRECTORY_SEPARATOR;
    }

    public function getGoogleProjectDocumentsFolderPath()
    {
        return $this->getDropboxFolderPath().$this::DOCUMENTS_FOLDER.DIRECTORY_SEPARATOR;
    }

    public function getListOfReportsFromGoogle()
    {
        $projectReportsPath = $this->getGoogleProjectReportsFolderPath();

        // this will create the directory in the google drive account
        $path = $this->getGooglePathFromHumanPath($projectReportsPath);

        $contents = collect(Storage::cloud()->listContents($path, false));

        $files = [];

        foreach ($contents as $file) {

            /*
               $file is something like:

               Array
                (
                    [name] => environmental_report.docx
                    [type] => file
                    [path] => 13pP-bjm4mnMFvj3Q6uqwHW9xXo3kW0Qp/19PpAVdMJVCV4gBeERPIhnyWXeNutwkhz/1O6Z1qGNGXXlFzzrvpADpdRXLYaDxKDTM/1TZr1iEtv9Aex-YZr0MlkdWlSYamM8t3n/1nwxaWxSOr9WCEU54_G_zVlNSPaXKstpT
                    [filename] => environmental_report
                    [extension] => docx
                    [timestamp] => 1574195592
                    [mimetype] => application/vnd.openxmlformats-officedocument.wordprocessingml.document
                    [size] => 738199
                    [dirname] => 13pP-bjm4mnMFvj3Q6uqwHW9xXo3kW0Qp/19PpAVdMJVCV4gBeERPIhnyWXeNutwkhz/1O6Z1qGNGXXlFzzrvpADpdRXLYaDxKDTM/1TZr1iEtv9Aex-YZr0MlkdWlSYamM8t3n
                    [basename] => 1nwxaWxSOr9WCEU54_G_zVlNSPaXKstpT
                )
             */

            $service = Storage::cloud()->getAdapter()->getService();
            $permission = new \Google_Service_Drive_Permission();
            $permission->setRole('reader');
            $permission->setType('anyone');
            $permission->setAllowFileDiscovery(false);
            $permissions = $service->permissions->create($file['basename'], $permission);

            /*
                    $permissions is now something like:

                    Google_Service_Drive_Permission Object
                    (
                        [collection_key:protected] => teamDrivePermissionDetails
                        [allowFileDiscovery] =>
                        [deleted] =>
                        [displayName] =>
                        [domain] =>
                        [emailAddress] =>
                        [expirationTime] =>
                        [id] => anyoneWithLink
                        [kind] => drive#permission
                        [permissionDetailsType:protected] => Google_Service_Drive_PermissionPermissionDetails
                        [permissionDetailsDataType:protected] => array
                        [photoLink] =>
                        [role] => reader
                        [teamDrivePermissionDetailsType:protected] => Google_Service_Drive_PermissionTeamDrivePermissionDetails
                        [teamDrivePermissionDetailsDataType:protected] => array
                        [type] => anyone
                        [internal_gapi_mappings:protected] => Array
                            (
                            )

                        [modelData:protected] => Array
                            (
                            )

                        [processed:protected] => Array
                            (
                            )

                    )
            */

            // This creates a downloadable link like:
            // https://drive.google.com/uc?id=1nwxaWxSOr9WCEU54_G_zVlNSPaXKstpT&export=media
            //
            // $link = Storage::cloud()->url($file['path']);
            //

            // I couldn't find a method to create this, I guess it's alright doing it this way...
            $link = 'https://docs.google.com/document/d/'.$file['basename'].'/edit';

            $files[] = [
                'name' => $file['name'],
                'link' => $link,

            ];
        }

        return $files;
    }

    public function getMediaPath($media)
    {
        $document = $media->model;
        $media_id = $media->id;

        $project_id = $this->id;
        $path = DIRECTORY_SEPARATOR.'projects'.DIRECTORY_SEPARATOR.$project_id.DIRECTORY_SEPARATOR.$document->type.
            DIRECTORY_SEPARATOR.$media_id.DIRECTORY_SEPARATOR;

        return $path;
    }

    public function boat()
    {
        return $this->belongsTo(\App\Models\Boat::class);
    }

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function siteLocation()
    {
        return $this->site()->select('sites.name', 'sites.location')->first();
    }

    // Alias che mi serve per compatibilità con API custom PR33
    public function location()
    {
        return $this->site();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Se utente non è storm, non vedrà i task privati
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasksWithVisibility()
    {
        $user = \Auth::user();
        if ($user && ! $user->is_storm) {
            return $this->tasks()->public();
        }

        return $this->tasks();
    }

    /**
     * Se utente non è storm, non vedrà i task privati
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasksNotDraftWithVisibility()
    {
        $tasksQuery = $this->tasksWithVisibilityExcludedStatus(TASKS_STATUS_DRAFT);
        if (request() && request()->has('filter')) { // todo_ viola l'mvc, ma tanto viene chiamata solo dalle API questa funzione
            $filters = request('filter');
            if (array_key_exists('task_type', $filters)) {
                $tasksQuery = $tasksQuery->where('task_type', $filters['task_type']);
            }
            if (array_key_exists('is_open', $filters)) {
                $tasksQuery = $tasksQuery->where('is_open', '=', $filters['is_open']);
            }
            if (array_key_exists('status', $filters)) {
                $tasksQuery = $tasksQuery->whereIn('task_status', explode('|', $filters['status']));
            }
            if (array_key_exists('intervent_types', $filters)) {
                $tasksQuery = $tasksQuery->whereIn('intervent_type_id', explode('|', $filters['intervent_types']));
            }
        }

        return $tasksQuery;
    }

    /**
     * Se utente non è storm, non vedrà i task privati
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasksWithVisibilityByStatus($status)
    {
        $user = \Auth::user();
        if ($user && ! $user->is_storm) {
            return $this->tasks()->where('task_status', '=', $status)->public();
        }

        return $this->tasks()->where('task_status', '=', $status);
    }

    /**
     * Se utente non è storm, non vedrà i task privati
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasksWithVisibilityExcludedStatus($status)
    {
        $user = \Auth::user();
        if ($user && ! $user->is_storm) {
            return $this->tasks()->where('task_status', '!=', $status)->public();
        }

        return $this->tasks()->where('task_status', '!=', $status);
    }

    /**
     * @return array
     */
    public function getAllTaskNumGroupedByStatus()
    {
        return array_map(function ($status) {
            return [$status => $this->tasksWithVisibilityByStatus($status)->count()];
        }, TASKS_STATUSES);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function history()
    {
        return $this->morphMany(\App\Models\History::class, 'historyable');
    }

//    public function sectionsOld()
//    {
//        return $this->belongsToMany('App\Models\Section')
//            ->using('App\Models\ProjectSection')
//            ->withPivot([
//                'project_id',
//                'section_id',
//                'created_at',
//                'updated_at'
//            ]);
//    }

    public function sections()
    {
        return $this->belongsToMany(\App\Models\Section::class, 'project_section', 'project_id', 'section_id')
            ->using(\App\Models\ProjectSection::class)
            ->withTimestamps()
            ->withPivot([
                'project_id',
                'section_id',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * @param int $sid
     *
     * @return BelongsToMany
     */
    public function getSectionByIdBaseQuery($sid)
    {
        return $this->sections()->where('sections.id', '=', $sid);
    }

    /**
     * @param int $sid
     *
     * @return User
     */
    public function getSectionById($sid)
    {
        return $this->getSectionByIdBaseQuery($sid)->first();
    }

    /**
     * @param int $sid
     *
     * @return bool
     */
    public function hasSectionById($sid)
    {
        return $this->getSectionByIdBaseQuery($sid)->count() > 0;
    }

    public function comments()
    {
        return $this->morphMany(\App\Models\Comment::class, 'commentable');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'project_user')
//                        ->using('App\ProjectUser')
            ->withPivot([
                // 'role',
                'profession_id',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'project_product')
            ->withPivot([
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * @return BelongsToMany
     */
    public function tools()
    {
        return $this->belongsToMany(\App\Models\Tool::class, 'project_tool')
            ->withPivot([
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * @param int $uid
     *
     * @return BelongsToMany
     */
    public function getUserByIdBaseQuery($uid)
    {
        return $this->users()->where('users.id', '=', $uid);
    }

    /**
     * @param int $uid
     *
     * @return User
     */
    public function getUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->first();
    }

    /**
     * @param int $uid
     *
     * @return bool
     */
    public function hasUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->count() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function application_logs()
    {
        return $this->hasMany(ApplicationLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function report_items()
    {
        return $this->hasMany(ReportItem::class);
    }

    /**
     * Chiude un progetto o tenta di chiuderlo se trova i task tutti chiusi
     * @param int $force
     * @return array
     */
    public function close($force = 0)
    {

        /** controllo se il progetto ha task che si trovano in
         *  TASKS_STATUS_DRAFT, TASKS_STATUS_IN_PROGRESS,
         *  TASKS_STATUS_ACCEPTED
         * * */
        $foundTasks = $this->tasks()->opened()
            ->whereIn('task_status',
                [
                    TASKS_STATUS_DRAFT,
                    TASKS_STATUS_SUBMITTED,
                    TASKS_STATUS_ACCEPTED,
                    TASKS_STATUS_IN_PROGRESS,
//                    TASKS_STATUS_MONITORED,
//                    TASKS_STATUS_REMARKED
                ]);

        if ($foundTasks->count()) {
            if (! $force) {
                // non posso chiudere il progetto ritorno false
                return ['success' => false, 'tasks' => $foundTasks->count()];
            }
        }

        // se non trovo ticket negli stati sopra, chiudo tutti i ticket APERTI (a prescindere dallo stato)
        foreach ($this->tasks()->opened()->get() as $task) {
            $task->update(['is_open' => 0]);
        }

        // ...e metto il progetto in stato closed
        $this->_closeProject();

        return ['success' => true, 'tasks' => $this->tasks()->opened()->count()];
    }

    /**
     * Setta lo stato del progetto a PROJECT_STATUS_CLOSED
     */
    private function _closeProject()
    {
        $this->update(['project_status' => PROJECT_STATUS_CLOSED]);
    }

    /**
     * Creates a Project using some fake data and some others that have sense
     *
     * @param Faker $faker
     * @param Site $site
     * @param Boat $boat
     *
     * @return Project $proj
     */
    public static function createSemiFake(Faker $faker, Site $site = null, Boat $boat = null)
    {
        $proj = new self([
                'name' => $faker->sentence(4),
                'start_date' => $faker->date(),
                'end_date' => $faker->date(),
                'project_type' => $faker->randomElement([PROJECT_TYPE_NEWBUILD, PROJECT_TYPE_REFIT]),
                'acronym' => $faker->word,
                'project_status' => $faker->randomElement([PROJECT_STATUS_IN_SITE, PROJECT_STATUS_OPERATIONAL, PROJECT_STATUS_CLOSED]),
                'site_id' => $site ? $site->id : null,
                'boat_id' => $boat ? $boat->id : null,
            ]
        );
        $proj->save();

        return $proj;
    }

    /**
     * Copies ProjectUser from project A to project B
     *
     * @param Project $project
     */
    public function transferMyUsersToProject(self $project)
    {
        if ($this->users()->count()) {
            foreach ($this->users as $user) {
                ProjectUser::createOneIfNotExists($user->id, $project->id, $user->pivot->profession_id);
            }
        }
    }

    public function checkForUpdatedFilesOnGoogleDrive()
    {
        $projectDocumentsPath = $this->getGoogleProjectDocumentsFolderPath();
        // this will create the directory in the google drive account
        $path = $this->getGooglePathFromHumanPath($projectDocumentsPath);

        $contents = collect(Storage::cloud()->listContents($path, false));

        $filenamesOnGoogle = [];

        foreach ($contents as $file) {
            $filenamesOnGoogle[] = $file['name'];

            $found = false;
            foreach ($this->generic_documents as $d) {
                if ($found) {
                    continue;
                }
                $timestamp = $file['timestamp'];
                $media = $d->getRelatedMedia();
                if ($file['name'] == $media->file_name) {
                    $found = true;
                    if ($timestamp > strtotime($media->updated_at)) {
                        // update file on disk and media updated_at in db
                        $rawData = Storage::cloud()->get($file['path']);
                        $base64FileContent = base64_encode($rawData);
                        $newfile = Document::createUploadedFileFromBase64($base64FileContent, $file['name']);
                        // we received the file from google drive, so we don't want to update it there as well
                        $this->updateDocument($d, $newfile, false);
                    }
                }
            }
            if (! $found) {
                // we didn't know about this file, so create the file in the DB

                if ($file['mimetype'] == 'application/vnd.google-apps.document') {
                    // use export for docs

                    $url = Storage::cloud()->url($file['path']);
                    $rawData =

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $rawData = curl_exec($ch);

                    curl_close($ch);
                } else {
                    $rawData = Storage::cloud()->get($file['path']);
                }
                $base64FileContent = base64_encode($rawData);
                $uploadedFile = Document::createUploadedFileFromBase64($base64FileContent, $file['name']);
                $cloudStorageData = [
                    'storage' => 'gDrive',
                    'path' => $projectDocumentsPath,
                    'filename' => $file['name'],
                ];
                $doc = new Document([
                    'title' => $file['name'],
                    'file' => $uploadedFile,
                    'cloud_storage_data' => json_encode($cloudStorageData),
                ]);
                // we received the file from google drive, so we don't want to update it there as well
                $this->addDocumentWithType($doc, Document::GENERIC_DOCUMENT_TYPE, false);
            }
        }

        // we remove entries from DB if the file doesn't exist anymore on google drive

        foreach ($this->generic_documents as $document) {
            $media = $document->getRelatedMedia();
            if ($media) {
                $cloudStorageData = json_decode($document->cloud_storage_data, true);
                if (isset($cloudStorageData) && isset($cloudStorageData['storage']) && $cloudStorageData['storage'] == 'gDrive' && ! in_array($media->file_name, $filenamesOnGoogle)) {
                    $document->delete();
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        $this->last_cloud_sync = $now;
        $this->save();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->where('project_status', '=', PROJECT_STATUS_CLOSED);
    }

    public static function closedProjects()
    {
        return self::closed()->get();
    }

    public static function closedProjectsFiltered($filters = [], $sortField = 'updated_at', $sortDir = 'desc')
    {
        $builder = self::with('boat', 'location')->closed();
        if (isset($filters['start_date'])) {
            $builder->where('start_date', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $builder->where('end_date', '<=', $filters['end_date']);
        }
        if (isset($filters['type'])) {
            $builder->where('project_type', $filters['type']);
        }
        if (isset($filters['boat_name'])) {
            $builder->whereHas('boat', function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['boat_name'].'%');
            });
        }

        return $builder->orderBy($sortField, $sortDir)->get();
    }

    /**
     * Get all NOT closed projects
     */
    public static function activeProjects()
    {
        return self::where('project_status', '!=', PROJECT_STATUS_CLOSED)->get();
    }

    /**
     * removes malevolent characters from path to be used on google drive, dropbox, etc.
     */
    private function sanitizePathForCloudStorages($path)
    {
        $malevolentCharacters = [
            "'", '*', '\\', '.', '"',
        ];

        return str_replace($malevolentCharacters, '', $path);
    }

    public function getGoogleSyncQueueName()
    {
        return 'project-google-sync-'.$this->id;
    }

    public function getGoogleSyncQueueSize()
    {

        // $queue = App::make('queue.connection');
        // $size = $queue->size($this->getGoogleSyncQueueName());

        //TODO: fix
        $size = Queue::size($this->getGoogleSyncQueueName());

        return $size;
    }

    public function getMeasurementLogsFullInfo($measurementLogDocument)
    {
        // select min(measurement_time), max(measurement_time) from net7em_measurements where document_id = 28 ;

        $measurement = new \Net7\EnvironmentalMeasurement\Models\Measurement();

        $min = DB::table($measurement->getTable())
            ->where('document_id', '=', $measurementLogDocument->id)
            ->min('measurement_time');

        $max = DB::table($measurement->getTable())
            ->where('document_id', '=', $measurementLogDocument->id)
            ->max('measurement_time');

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * @param $collection
     * @param $page_param
     * @return array
     */
    protected static function getPaginationResponseTags($collection, $page_param)
    {
        $page = $page_param['number'];
        $per_page = $page_param['size'];
        $page_size_param = '&page[size]='.$per_page;
        // io già la usavo su /api/v1/tasks?page[number]=3&page[size]=1
        $json_reports_array = json_decode($collection->toJson(), 1);

//      trasforma  "http://storm.zoba/api/v1/projects/1/reports-list?page=2" in "http://storm.zoba/api/v1/projects/1/reports-list?page[number]=2"
        $pattern = "/([a-zA-Z0-9-.%:\/]*[?][a-zA-Z0-9-.%:=\/&]*)(page=)([0-9]+)/i";
        $replace = '$1page[number]=$3';

        $ret = [
            'meta' => [
                'page' => [
                    'current-page' => $collection->currentPage(),
                    'per-page' => $per_page,
                    'from' => $collection->firstItem(),
                    'to' => $collection->lastItem(),
                    'total' => $collection->total(),
                    'last-page' => $collection->lastPage(),
                ],
            ],
            'links' => [
                'first' => $json_reports_array['first_page_url'] ? preg_replace($pattern, $replace, $json_reports_array['first_page_url']).$page_size_param : null,
                'prev' => $json_reports_array['prev_page_url'] ? preg_replace($pattern, $replace, $json_reports_array['prev_page_url']).$page_size_param : null,
                'next' => $json_reports_array['next_page_url'] ? preg_replace($pattern, $replace, $json_reports_array['next_page_url']).$page_size_param : null,
                'last' => $json_reports_array['last_page_url'] ? preg_replace($pattern, $replace, $json_reports_array['last_page_url']).$page_size_param : null,
            ],
        ];

        return $ret;
    }

    /**
     * @param null $page_param
     * @return array
     */
    public function getReportsLinks($page_param = null)
    {
        if ($page_param) {
            $page = $page_param['number'];
            $per_page = $page_param['size'];
            $reports = $this
                ->getDocumentsByTypeQuery(self::REPORT_DOCUMENT_TYPE, 'created_at', 'desc')
                ->paginate($per_page, ['*'], 'page', $page);
            $ret = self::getPaginationResponseTags($reports, $page_param);
        } else {
            $reports = $this
                ->getDocumentsByTypeQuery(self::REPORT_DOCUMENT_TYPE, 'created_at', 'desc')
                ->get();
        }

        $gdrive_links = [];
        foreach ($reports as $report) {
            $data = json_decode($report->cloud_storage_data, true);
            $gdrive_links[] = [
                'upload_date' => $report->created_at,
                'link' => $data['gdrive_link'],
                'name' => $data['gdrive_filename'],
                'title' => $report->title,
                'subtype' => $report->subtype,
                'id' => $report->id,
            ];
        }

        $ret['data'] = $gdrive_links;

        return $ret;
//        return $this->getListOfReportsFromGoogle();
    }

    /**
     * @param null $page_param
     * @return array
     */
    public function getMeasurementLogsData($page_param = null)
    {
        if ($page_param) {
            $measurement_logs = $this
                ->getDocumentsByTypeQuery(MEASUREMENT_FILE_TYPE, 'created_at', 'desc')
                ->paginate($page_param['size'], ['*'], 'page', $page_param['number']);
            $ret = self::getPaginationResponseTags($measurement_logs, $page_param);
        } else {
            $measurement_logs = $this
                ->getDocumentsByTypeQuery(MEASUREMENT_FILE_TYPE, 'created_at', 'desc')
                ->get();
        }

        $measurement_logs_data = [];
        foreach ($measurement_logs as $measurement_log) {
            $minmax = $this->getMeasurementLogsFullInfo($measurement_log);
            $additional_data = @json_decode($measurement_log->additional_data, true);
            $measurement_logs_data[] = [
                'id' => $measurement_log->id,
                'upload_date' => $measurement_log->created_at,
                'data_source' => @$additional_data['data_source'],
                'start_date' => gmdate('Y-m-d\TH:i:s\.000000\Z', strtotime($minmax['min'])),
                'end_date' => gmdate('Y-m-d\TH:i:s\.000000\Z', strtotime($minmax['max'])),
            ];
        }

        $ret['data'] = $measurement_logs_data;

        return $ret;
    }

    /**
     * @return mixed
     */
    public function measurementLogs()
    {
        return $this->documents()->where('type', MEASUREMENT_FILE_TYPE);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getParentZonesQuery()
    {
        return $this->zones()->whereNull('parent_zone_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getParentZones()
    {
        return $this->getParentZonesQuery()->get();
    }

    /**
     * @return int
     */
    public function countParentZones()
    {
        return $this->getParentZonesQuery()->count();
    }

    /**
     * @param $data
     * @param array $excluded_ids
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getParentZonesByDataQuery($data, $excluded_ids = [])
    {
        $ret = $this->getParentZonesQuery()->where($data);
        if ($excluded_ids) {
            $ret->whereNotIn('id', $excluded_ids);
        }

        return $ret;
    }

    /**
     * @param $data
     * @param array $excluded_ids
     * @return int
     */
    public function countParentZonesByData($data, $excluded_ids = [])
    {
        return $this->getParentZonesByDataQuery($data, $excluded_ids)->count();
    }

    /**
     * @param $parent_zone_id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getChildrenZonesQuery($parent_zone_id)
    {
        return $this->zones()->where('parent_zone_id', '=', $parent_zone_id);
    }

    /**
     * @param $parent_zone_id
     * @param $data
     * @param array $excluded_ids
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getChildrenZonesByDataQuery($parent_zone_id, $data, $excluded_ids = [])
    {
        $ret = $this->getChildrenZonesQuery($parent_zone_id)->where($data);
        if ($excluded_ids) {
            $ret->whereNotIn('id', $excluded_ids);
        }

        return $ret;
    }

    /**
     * @param $parent_zone_id
     * @param $data
     * @param array $excluded_ids
     * @return int
     */
    public function countChildrenZonesByData($parent_zone_id, $data, $excluded_ids = [])
    {
        return $this->getChildrenZonesByDataQuery($parent_zone_id, $data, $excluded_ids)->count();
    }

    /**
     * Copies Zones from project A to project B
     *
     * @param Project $project
     */
    public function transferMyZonesToProject(self $project)
    {
        if ($this->zones()->count()) {
            foreach ($this->getParentZones() as $p_zone) {
                $new_p_zone = Zone::create(
                    [
                        'project_id' => $project->id,
                        'code' => $p_zone->code,
                        'description' => $p_zone->description,
                        'extension' => $p_zone->extension,
                    ]
                );

                foreach ($p_zone->children_zones as $c_zone) {
                    Zone::create(
                        [
                            'parent_zone_id' => $p_zone->id,
                            'project_id' => $project->id,
                            'code' => $c_zone->code,
                            'description' => $c_zone->description,
                            'extension' => $c_zone->extension,
                        ]
                    );
                }
            }
        }
    }

    /**
     * An internal ID calculated on a "per-boat" base
     *
     * @param $boat_id
     * @return int
     */
    public static function getLastInternalProgressiveIDByBoat($boat_id)
    {
        $max = self::where('projects.boat_id', '=', $boat_id)->max('projects.internal_progressive_number');

        return $max ? $max : 0;
    }

    /**
     * Gives total number of projects calculated on a "per-boat" base
     *
     * @param $boat_id
     * @return int
     */
    public static function countProjectsByBoat($boat_id)
    {
        return self::where('projects.boat_id', '=', $boat_id)->count();
    }

    /**
     * @return void
     */
    public function updateInternalProgressiveNumber()
    {
        if (env('INTERNAL_PROG_NUM_ACTIVE')) {
            $p_boat = $this->boat;
            if ($p_boat) {
                $highest_internal_pn = self::getLastInternalProgressiveIDByBoat($p_boat->id);
                $this->update(['internal_progressive_number' => ++$highest_internal_pn]);
            }
        }
    }

    /**
     * @return Writer
     * @throws \League\Csv\CannotInsertRecord
     */
    public function extractCsvFile()
    {
        $header = [
            'Point ID',
            'Description',
            'Location',
            'Type',
            'Status',
            'Created At',
            'Application log - opened',
            'Application log - closed',
            'Zone',
        ];
        // per ogni application log, devo verificare se il task fa parte dei task inclusi
//        $applicationLogs = $this->application_logs;
//        ApplicationLog::where('project_id', '=', $this->id)
//            ->whereHas('opened_tasks', function () {
//
//            });

        $records = collect($this->getTasksToIncludeInReport())->map(fn ($task) => [
            $task->internal_progressive_number,
            sanitizeTextsForPlaceholders($task->description),
            $task->section ? sanitizeTextsForPlaceholders($task->section->name) : '?',
            ($task->task_type == TASK_TYPE_PRIMARY) ? ($task->intervent_type ? sanitizeTextsForPlaceholders($task->intervent_type->name) : '?') : 'Remark',
            $task->task_status,
            ($task->task_type == TASK_TYPE_PRIMARY) ? date('d M Y', strtotime($task->created_at)) : $task->created_at->format('d M Y'),
            implode('|', $task->opener_application_log()->pluck('name')->toArray()),
            implode('|', $task->closer_application_log()->pluck('name')->toArray()),
            $task->zone_text,
        ]);

        return createCsvFileFromHeadersAndRecords($header, $records);
    }
}
