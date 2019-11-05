<?php

namespace App;

use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\ModelStatus\HasStatuses;
use Faker\Generator as Faker;
use Net7\DocsGenerator\Traits\HasDocsGenerator;

use App\Task;
use \Net7\Documents\DocumentableTrait;
use \Net7\Documents\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Support\Facades\Queue;

class Project extends Model {

    use DocumentableTrait {
         addDocumentWithType as traitAddDocumentWithType;
         updateDocument as traitUpdateDocument;
    }
    use HasStatuses, SerializesModels,  HasDocsGenerator;

    protected $table = 'projects';
    protected $fillable = [
        'name', 'project_status', 'boat_id', 'project_type', 'project_progress', 'site_id', 'start_date', 'end_date', 'imported'
    ];

    // Usate con il DocsGenerator
    protected $_currentTask;
    protected $_currentTaskPhotos;
    protected $_taskToIncludeInReport;
    protected $_openHandles = [];

    protected static function boot() {
        parent::boot();

        Project::observe(ProjectObserver::class);
    }

    public function sendDocumentToDropbox(\Net7\Documents\Document $document){

        $document = Document::find($document->id);
        $media = $document->getRelatedMedia();

        $filepath = $media->getPath();

        $fh = fopen($filepath, 'r');
        $content = fread($fh, filesize($filepath));
        fclose ($fh);

        $filename = $media->file_name;
        $dropboxFolder =  $this->getDropboxFolderPath($document);
        $dropboxFilepath =  $this->getDropboxFilePath($document, $filename);


        $client = new \Spatie\Dropbox\Client(env('DROPBOX_TOKEN'));
        try {
            $client->listFolder($dropboxFolder);
        } catch ( \Spatie\Dropbox\Exceptions\BadRequest  $e) {
            $client->createFolder($dropboxFolder);
        }

        // try {
            $client->upload($dropboxFilepath, $content, 'add');
        // } catch (Exception $e){

        // }

        // $client->getMetadata($fullPath);

        // TODO: remove local file

        // TODO: check for errors

        // TODO: finish it up
    }

    public function getGooglePathFromHumanPath($path){
        $lastPath = '';
        $contents = collect(Storage::cloud()->listContents($lastPath, false));

        $lastDir = false;
        $folderSteps = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($folderSteps as $step){

            // apparently we can't just ask google drive for subdirectories, like
            // '/boats/Pinta/3'
            // we need to cycle dir by dir and use their IDs to identify the directories

            if (!trim($step)) {
                // sometimes there are empty steps, go figure
                continue;
            }

            $dir = $contents->where('type', '=', 'dir')->where('filename', '=', $step)->first();

            if (!$dir){
                // we need to create it
                $path ='';
                if ($lastDir) {
                    $path .= $lastPath . '/';
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

    public function sendDocumentToGoogleDrive(\Net7\Documents\Document $document){

        $document = Document::find($document->id);
        $media = $document->getRelatedMedia();

        $filepath = $media->getPath();

        $fh = fopen($filepath, 'r');
        $content = fread($fh, filesize($filepath));
        fclose ($fh);

        $filename = $media->file_name;
        $googleFolder =  $this->getGoogleFolderPath($document);
        $filename =  $this->getGoogleFilename($document, $filename);

        $path = $this->getGooglePathFromHumanPath($googleFolder);

        // now we have the full path made of directory Ids, we can upload our file there.


        $path .=  '/'. $filename;
        Storage::cloud()->put($path, $content);

        //TODO: check errors?


    }

    /**
     *
     * @Override the base method to send the updated files to dropbox
     */
    public function updateDocument(\Net7\Documents\Document $document, $file, $useCloud = true){

        $this->traitUpdateDocument( $document, $file);

        if ($useCloud){
            if (env('USE_DROPBOX')) {
                $this->sendDocumentToDropbox($document);
            }

            if (env('USE_GOOGLE_DRIVE')){
                $this->sendDocumentToGoogleDrive($document);

            }
        }
    }

    /**
     *
     * @Override the base method to send files to dropbox
     */


    public function addDocumentWithType(\Net7\Documents\Document $document, $type, $useCloud = true) {

        $this->traitAddDocumentWithType($document, $type);

        // TODO: spostarlo in un job per le code

        $this->save();
        $document->refresh();

        if ($useCloud){
            if (env('USE_DROPBOX')) {
                $this->sendDocumentToDropbox($document);
            }

            if (env('USE_GOOGLE_DRIVE')){
                $this->sendDocumentToGoogleDrive($document);

            }
        }
    }

    public function getDocumentFromDropbox(\Net7\Documents\Document $document){


        $media = $document->getRelatedMedia();
        $filename = $media->file_name;
        $dropboxFolder =  $this->getDropboxFolderPath($document);
        $dropboxFilepath =  $this->getDropboxFilePath($document, $filename);


        $client = new \Spatie\Dropbox\Client(env('DROPBOX_TOKEN'));

        $link = $client->getTemporaryLink($dropboxFilepath );

        return $link;
    }


    public function getDocumentFromGoogle(\Net7\Documents\Document $document){


        $media = $document->getRelatedMedia();
        $filename = $media->file_name;


        $cloudStorageData = json_decode($document->cloud_storage_data, true);

        if (isset($cloudStorageData['path'])) {
            $googleFolder = $cloudStorageData['path'];
        } else {
            $googleFolder =  $this->getGoogleFolderPath($document);
        }
        if (isset($cloudStorageData['filename'])) {
            $filename = $cloudStorageData['filename'];
        } else {
            $filename =  $this->getGoogleFilename($document, $filename);

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
*/


        return $link;

    }



    public function getRelatedMedia(){
        // return $this->media;
    }

    public function getDropboxFilename($document, $filename){
        $media = $document->getRelatedMedia();

        $path_parts = pathinfo($this->getMediaPath($media) . $filename);

        $basename = $path_parts['filename'];
        $extension = $path_parts['extension'];
        return $basename . '_' . $media->id . '.' . $extension;
    }

    public function getDropboxFilePath ($document, $filename){
        $path = $this->getDropboxFolderPath($document) .$this->getDropboxFilename($document, $filename);
        return $this->sanitizePathForCloudStorages($path);
    }

    public function getDropboxFolderPath($document = false){

        $boat = $this->boat;
        $project_id = $this->id;
        $boat_name = $boat->name;

        $path = '';
        if (env('CLOUD_BASE_DIR')) {
            $path .= DIRECTORY_SEPARATOR . env('CLOUD_BASE_DIR');
        }

        $path .= DIRECTORY_SEPARATOR .'boats' . DIRECTORY_SEPARATOR .$boat_name . '_'. sprintf("%07d", $project_id)   . '_' .
        $this->project_type. '_' . date ('Y-m-d', strtotime($this->created_at)). DIRECTORY_SEPARATOR;

        if ( $document && $document->document_number) {
            $path .= $document->document_number . DIRECTORY_SEPARATOR;
        }
        return $this->sanitizePathForCloudStorages($path);

    }

    public function getGoogleFilename($document, $filename){
        return $this->getDropboxFilename($document, $filename);
    }

    // public function getGoogleFilePath ($document, $filename){
    //     return $this->getDropboxFilePath ($document, $filename);
    // }

    public function getGoogleFolderPath($document){
        return $this->getDropboxFolderPath($document);
    }

    public function getGoogleProjectDocumentsFolderPath(){
        return $this->getDropboxFolderPath() . 'documents' . DIRECTORY_SEPARATOR;
    }



    public function getMediaPath($media){

        $document = $media->model;
        $media_id = $media->id;

        $project_id = $this->id;
        $path = DIRECTORY_SEPARATOR .'projects' . DIRECTORY_SEPARATOR . $project_id . DIRECTORY_SEPARATOR . $document->type .
                 DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;

    }

    public function boat() {
        return $this->belongsTo('App\Boat');
    }

    public function site() {
        return $this->belongsTo('App\Site');
    }

    public function siteLocation() {
        return $this->site()->select('sites.name', 'sites.location')->first();
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }

    public function history() {
        return $this->morphMany('App\History', 'historyable');
    }

    public function sections() {
        return $this->belongsToMany('App\Section')
                        ->using('App\ProjectSection')
                        ->withPivot([
                            'project_id',
                            'section_id',
                            'created_at',
                            'updated_at'
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
     * @return Boolean
     */
    public function hasSectionById($sid)
    {
        return $this->getSectionByIdBaseQuery($sid)->count() > 0;
    }


    public function comments() {
        return $this->morphMany('App\Comment', 'commentable');
    }


    public function users() {
        return $this->belongsToMany('App\User', 'project_user')
//                        ->using('App\ProjectUser')
                        ->withPivot([
                            // 'role',
                            'profession_id',
                            'created_at',
                            'updated_at'
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
     * @return Boolean
     */
    public function hasUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->count() > 0;
    }


    /**
     * Chiude un progetto o tenta di chiuderlo se trova i task tutti chiusi
     * @param type $force
     */
    public function close($force = 0) {

        /** controllo se il progetto ha task che si trovano in
         *  TASKS_STATUS_DRAFT, TASKS_STATUS_IN_PROGRESS,
         *  TASKS_STATUS_REMARKED, TASKS_STATUS_ACCEPTED
         * * */
        $foundTasks = $this->tasks()
                        ->where('is_open', '=', 1)
                        ->whereIn('task_status',
                          [
                            TASKS_STATUS_DRAFT,
                            TASKS_STATUS_SUBMITTED,
                            TASKS_STATUS_ACCEPTED,
                            TASKS_STATUS_IN_PROGRESS,
                            TASKS_STATUS_REMARKED
                           ]);


        if ($foundTasks->count() && !$force) {
            // non posso chiudere il progetto ritorno false
            return ['success'=>false, 'tasks'=>$foundTasks->count()];
        }

        if ($foundTasks->count() && $force) {
            // chiudo tutti i ticket che trovo e metto il progetto in stato closed
            $n =  $foundTasks->count();
            foreach ($foundTasks->get() as $task) {
                $task->update(['is_open'=>0]);
            }
            $this->_closeProject();
           return ['success'=>true, 'tasks'=>$n];
        }

        if ($foundTasks->count() == 0) {
            // chiudo il progetto e ritorno true
            $this->_closeProject();
            return ['success'=>true, 'tasks'=>$foundTasks->count()];
        }
    }

    /**
     * Setta lo stato del progetto a PROJECT_STATUS_CLOSED
     */
    private function _closeProject() {
        $this->update(['project_status'=>PROJECT_STATUS_CLOSED]);
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
        $proj = new Project([
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
    public function transferMyUsersToProject(Project $project)
    {
        if ($this->users()->count()) {
            foreach ($this->users as $user) {
                ProjectUser::createOneIfNotExists($user->id, $project->id, $user->pivot->profession_id);
            }
        }
    }

    public function checkForUpdatedFilesOnGoogleDrive(){

        $projectDocumentsPath = $this->getGoogleProjectDocumentsFolderPath();
        // this will create the directory in the google drive account
        $path = $this->getGooglePathFromHumanPath($projectDocumentsPath);

        $contents = collect(Storage::cloud()->listContents($path, false));

        $filenamesOnGoogle = [];

        foreach ($contents as $file){

            $filenamesOnGoogle []= $file['name'];

            $found = false;
            foreach ($this->generic_documents as $d){
                if ($found) {
                    continue;
                }
                $timestamp = $file['timestamp'];
                $media = $d->getRelatedMedia();
                if ($file['name'] == $media->file_name) {
                    $found = true;
                    if ($timestamp > strtotime($media->updated_at)){
                        // update file on disk and media updated_at in db
                        $rawData = Storage::cloud()->get($file['path']);
                        $base64FileContent = base64_encode($rawData);
                        $newfile = Document::createUploadedFileFromBase64($base64FileContent, $file['name']);
                        // we received the file from google drive, so we don't want to update it there as well
                        $this->updateDocument($d, $newfile, false);

                    }
                }
            }
            if (!$found){
                    // we didn't know about this file, so create the file in the DB
                    $rawData = Storage::cloud()->get($file['path']);

                    $base64FileContent = base64_encode($rawData);
                    $uploadedFile = Document::createUploadedFileFromBase64($base64FileContent,  $file['name']);
                    $cloudStorageData = [
                        'storage' => 'gDrive',
                        'path' =>  $projectDocumentsPath,
                        'filename' =>  $file['name']
                    ];
                    $doc = new Document([
                        'title' => $file['name'],
                        'file' => $uploadedFile,
                        'cloud_storage_data' => json_encode($cloudStorageData)
                    ]);
                    // we received the file from google drive, so we don't want to update it there as well
                    $this->addDocumentWithType($doc, Document::GENERIC_DOCUMENT_TYPE, false);
            }

        }

        // we remove entries from DB if the file doesn't exist anymore on google drive

        foreach ($this->generic_documents as $document){

            $media = $document->getRelatedMedia();
            if ($media) {
                $cloudStorageData = json_decode($document->cloud_storage_data, true);
                if (isset($cloudStorageData) && isset($cloudStorageData['storage']) && $cloudStorageData['storage'] == 'gDrive' && !in_array( $media->file_name, $filenamesOnGoogle)) {
                    $document->delete();
                }
            }

        }

        $now =  date("Y-m-d H:i:s");
        $this->last_cloud_sync = $now;
        $this->save();
    }


    /**
     * Get all closed projects
     */
    public static function closedProjects()
    {
        return Project::where('project_status', '=', PROJECT_STATUS_CLOSED)->get();
    }

    /**
     * Get all NOT closed projects
     */
    public static function activeProjects()
    {
        return Project::where('project_status', '!=', PROJECT_STATUS_CLOSED)->get();
    }


    /**
     * removes malevolent characters from path to be used on google drive, dropbox, etc.
     */
    private function sanitizePathForCloudStorages($path){

        $malevolentCharacters = [
            "'", "*", "\\", ".", "\""
        ];

        return str_replace($malevolentCharacters, '', $path);
    }

    public function getGoogleSyncQueueName(){

        return 'project-google-sync-'.$this->id;
    }

    public function getGoogleSyncQueueSize(){

        // $queue = App::make('queue.connection');
        // $size = $queue->size($this->getGoogleSyncQueueName());

//TODO: fix
        $size = Queue::size($this->getGoogleSyncQueueName());
        return $size;

    }


    /*  methods for net7 docs-generator templates */

    public function getBoatName(){
        $boat = $this->boat;
        return $boat->name;
    }

    public function getBoatRegistrationNumber(){
        $boat = $this->boat;
        return $boat->registration_number;
    }

    public function getBoatType(){
        $boat = $this->boat;
        return $boat->boat_type;
    }

    public function getBoatMainPhotoPath(){

        $boat = $this->boat;
        return $boat->getMainPhotoPath();
    }


    public function printDocxPageBreak()
    {
        return '</w:t></w:r>'.'<w:r><w:br w:type="page"/></w:r>'. '<w:r><w:t>';
    }

    public function printDocxTodayDate()
    {
        return date('Y-m-d', time());
    }

    public function getBloccoTaskSampleReportInfoArray()
    {
        $replacements = [];
        foreach ($this->getTasksToIncludeInReport() as $task) {
            $this->_currentTask = $task;
            $this->updateCurrentTaskPhotosArray();
            $replacements[] =
                [
                    'task_id' => $task->id,
                    'task_status' => $task->task_status,
                    'task_description' => $task->description,
                    'task_created_at' => $task->created_at,
                    'task_updated_at' => $task->updated_at,
                    'task_type' => $task->intervent_type ? $task->intervent_type->name : '?',
                    'task_location' => $task->section ? $task->section->name : '?',
                    'img_currentTaskBridgeImage' => $this->getCurrentTaskBridgeImage(),
                    'pageBreak' => $this->printDocxPageBreak(),
                    'img_currentTask_img1' => $this->getCurrentTaskImg1(),
                    'img_currentTask_img2' => $this->getCurrentTaskImg2(),
                    'img_currentTask_img3' => $this->getCurrentTaskImg3(),
                    'img_currentTask_img4' => $this->getCurrentTaskImg4(),
                    'img_currentTask_img5' => $this->getCurrentTaskImg5(),
                ]
            ;
        }
        return $replacements;
    }

    public function getCurrentTaskImg($index)
    {
        return isset($this->_currentTaskPhotos[$index]) ? $this->_currentTaskPhotos[$index] : '';
    }

    public function getCurrentTaskImg1()
    {
        return $this->getCurrentTaskImg(1);
    }

    public function getCurrentTaskImg2()
    {
        return $this->getCurrentTaskImg(2);
    }

    public function getCurrentTaskImg3()
    {
        return $this->getCurrentTaskImg(3);
    }

    public function getCurrentTaskImg4()
    {
        return $this->getCurrentTaskImg(4);
    }

    public function getCurrentTaskImg5()
    {
        return $this->getCurrentTaskImg(5);
    }

    public function updateCurrentTaskPhotosArray()
    {
        if ($this->_currentTask) {
            $index = 1;
            foreach ($this->_currentTask->getDetailedPhotoPaths() as $path) {
                $this->_currentTaskPhotos[$index++] = $path;
            }
            $this->_currentTaskPhotos[$index] = $this->_currentTask->getAdditionalPhotoPath();
        }
    }

    public function getCurrentTaskBridgeImage(){
        if ($this->_currentTask && $this->_currentTask->bridge_position) {
            $data =  $this->_currentTask->generateBridgePositionFileFromBase64();

            $this->_openHandles []= $data['handle'];
            return $data['path'];
        }

        return '';
    }

    public function setTasksToIncludeInReport($tasks){
        $this->_taskToIncludeInReport = $tasks;
    }

    public function getTasksToIncludeInReport(){
        if ($this->_taskToIncludeInReport){
            $tasks = [];
            foreach($this->_taskToIncludeInReport as $task_id){
                $tasks []= Task::Find($task_id);
            }
            return $tasks;
        } else {
            return $this->tasks;
        }


    }

    public function closeAllTasksTemporaryFiles(){
        foreach ($this->_openHandles as $handle){

            fclose($handle);
        }
    }
}

