<?php

namespace App;

use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Model;
use function is_object;
use Spatie\ModelStatus\HasStatuses;
use StormUtils;
use Venturecraft\Revisionable\RevisionableTrait;
use function GuzzleHttp\json_decode;
use App\Document;
use Faker\Generator as Faker;


class Task extends Model
{  
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

    public function author()
    {
        return $this->belongsTo('App\User');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function documents(){
            return $this->morphMany('App\Document', 'documentable');
    }
    
    public function addDocumentWithType(Document $doc, $type){
        if ($type){
            $doc->type = $type;
        } else {
            $doc->type = \App\Document::GENERIC_DOCUMENT_TYPE;
        }
        $this->documents()->save($doc);

    }
    
    
    public function detailed_images(){ 
        return $this->documents()->where('type', \App\Document::DETAILED_IMAGE_TYPE);
    }

    public function additional_images(){ 
        return $this->documents()->where('type', \App\Document::ADDITIONAL_IMAGE_TYPE);
    }

    public function generic_images(){ 
        return $this->documents()->where('type', \App\Document::GENERIC_IMAGE_TYPE);
    }

    public function generic_documents(){ 
        return $this->documents()->where('type', \App\Document::GENERIC_DOCUMENT_TYPE);
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

        $t = new Task([
                'number' => $faker->randomDigitNotNull(),
                'title' => $faker->sentence(),
                'description' => $faker->text(),
                'estimated_hours' => $faker->randomFloat(1, $min = 0, $max = 100),
                'worked_hours' => $faker->randomFloat(1, $min = 0, $max = 100),
                'x_coord' => $faker->randomFloat(2, $min = 0, $max = 100),
                'y_coord' => $faker->randomFloat(2, $min = 0, $max = 100),
                'task_status' => $faker->randomElement(TASKS_STATUSES),
                'project_id' => $proj ? $proj->id : null,
                'section_id' => $sect ? $sect->id : null,
                'subsection_id' => $ssect ? $ssect->id : null,
                'author_id' => $author ? $author->id : null,
                'intervent_type_id' => $type ? $type->id : null,
            ]
        );
        $t->save();

        return $t;
    }
}
