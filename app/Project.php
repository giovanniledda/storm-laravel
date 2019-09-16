<?php

namespace App;

use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\ModelStatus\HasStatuses;
use Faker\Generator as Faker;

use \Net7\Documents\DocumentableTrait;
use \Net7\Documents\Document;

class Project extends Model {

    use DocumentableTrait {
         addDocumentWithType as traitAddDocumentWithType;
    }
    use HasStatuses;

    protected $table = 'projects';
    protected $fillable = [
        'name', 'project_status', 'boat_id', 'project_type', 'project_progress', 'site_id', 'start_date', 'end_date'
    ];

    protected static function boot() {
        parent::boot();

        Project::observe(ProjectObserver::class);
    }



    /**
     *
     * @Override the base method to send files to dropbox
     */

    public function addDocumentWithType(\Net7\Documents\Document $doc, $type) {

        $this->traitAddDocumentWithType($doc, $type);

        // TODO: spostarlo in un job per le code

        $this->save();
        $doc->refresh();

        $doc = Document::find($doc->id);
        $media = $doc->getFirstMedia('documents');

        // $folder = $this->getMediaPath($media);
        $filepath = $media->getPath();




        // $client->listFolder($folder);

        // $filename = 'Zippo Case.pdf';

        // $filepath = '../Zippo Case.pdf';


        //$base_path =

        // $filepath = $folder . $filename;

        $fh = fopen($filepath, 'r');

        $content = fread($fh, filesize($filepath));

        fclose ($fh);
        // $fullPath = $folder . DIRECTORY_SEPARATOR . $filename;

        $filename = $media->file_name;
        $dropboxFolder =  $this->getDropboxFolderPath();
        $dropboxFilepath =  $this->getDropboxFilePath($media, $filename);


        $doc->external_path = $dropboxFilepath;
        $doc->save();

        $client = new \Spatie\Dropbox\Client(env('DROPBOX_TOKEN'));
        try {
            $client->listFolder($dropboxFolder);
        } catch ( \Spatie\Dropbox\Exceptions\BadRequest  $e) {
            $client->createFolder($dropboxFolder);
        }

        $client->upload($dropboxFilepath, $content, 'add');

        // $client->getMetadata($fullPath);

        // TODO: remove local file

        // TODO: check for errors

        // TODO: finish it up

    }

    public function getDocumentFromDropbox(\Net7\Documents\Document $document){


        $media = $document->getRelatedMedia();
        $filename = $media->file_name;
        $dropboxFolder =  $this->getDropboxFolderPath();
        $dropboxFilepath =  $this->getDropboxFilePath($media, $filename);


        $client = new \Spatie\Dropbox\Client(env('DROPBOX_TOKEN'));


        $link = $client->getTemporaryLink($dropboxFilepath );

        return $link;
    }

    public function getRelatedMedia(){
        // return $this->media;
    }

    public function getDropboxFilePath ($media, $filename){
        return $this->getDropboxFolderPath($media) .  $media->id . '_' . $filename;
    }

    public function getDropboxFolderPath(){

        $boat = $this->boat;
        $project_id = $this->id;
        $boat_name = $boat->name;

        $path = DIRECTORY_SEPARATOR .'boats' . DIRECTORY_SEPARATOR .$boat_name . '_'. $project_id   . '_' .
            $this->start_date. DIRECTORY_SEPARATOR;

        return $path;

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
        return $this->belongsToMany('App\User')
                        ->using('App\ProjectUser')
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
}
