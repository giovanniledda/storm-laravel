<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function is_object;
use Spatie\ModelStatus\HasStatuses;
use StormUtils;
use Venturecraft\Revisionable\RevisionableTrait;
use function GuzzleHttp\json_decode;

class Task extends Model
{

    public const DETAILED_IMAGE_TYPE = 'task_detailed_image';
    public const GENERIC_IMAGE_TYPE = 'task_generic_image';
    public const ADDITIONAL_IMAGE_TYPE = 'task_additional_image';

    use RevisionableTrait, HasStatuses;

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
    ];


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

    public function author()
    {
        return $this->belongsTo('App\User');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function addDetailedImage(\App\Document $doc){
        $doc->type = self::DETAILED_IMAGE_TYPE;
        $this->documents()->save($doc);
    }

    public function addAdditionalImage(\App\Document $doc){
        $doc->type = self::ADDITIONAL_IMAGE_TYPE;
        $this->documents()->save($doc);
    }

    public function addGenericImage(\App\Document $doc){
        $doc->type = self::GENERIC_IMAGE_TYPE;
        $this->documents()->save($doc);
    }

    public function addGenericDocument(\App\Document $doc){
        $doc->type = Document::GENERIC_DOCUMENT_TYPE;
        $this->documents()->save($doc);
    }


    public function addDocumentWithType(\App\Document $doc, $type){
        $doc->type = $type;
        $this->documents()->save($doc);

    }
    public function detailed_images(){
        return $this->documents()->where('type', self::DETAILED_IMAGE_TYPE);
    }

    public function additional_images(){
        return $this->documents()->where('type', self::ADDITIONAL_IMAGE_TYPE);
    }

    public function generic_images(){
        return $this->documents()->where('type', self::GENERIC_IMAGE_TYPE);
    }

    public function generic_documents(){
        return $this->documents()->where('type', Document::GENERIC_DOCUMENT_TYPE);
    }

    public function documents(){
            return $this->morphMany('App\Document', 'documentable');
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
    public function getUsersToNotify() {

// aggiungere qua altra logica, se serve (tipo filtri sui ruoli, etc)
//        return StormUtils::getAllBoatManagers();
        return $this->getProjectUsers();
    }

}
