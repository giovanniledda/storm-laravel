<?php

namespace App\JsonApi\V1\Tasks; 
use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Adapter extends AbstractAdapter
{

    protected $fillable = [
        'number',
        'title',
        'status',
        'description',
        'estimated_hours',
        'worked_hours',
        'for_admins',
        'project_id',
        'section_id',
        'intervent_type_id', 
        'subsection_id',
        'x_coord',
        'y_coord',
        'is_open',
        ];

    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * @var array
     */
     
    // mappa il nome della proprieta della risorsa API con il nome del campo nel database
     protected $attributes = ['status'=> 'task_status'];

    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging)
    {
        parent::__construct(new \App\Task(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        // TODO IMPLEMENTARE L'ACCESSO HAI TASK A LIVELLO DI ACL
       // ricerca per status
       if ($status = $filters->get('status')) {
            $query->where('task_status', '=', "{$status}");
       } 
       // ricerca per project_id
       if ($project_id = $filters->get('project_id')) {
            $query->where('project_id', '=', "{$project_id}");
       }
       
       // ricerca per section_id
       if ($section_id = $filters->get('section_id')) {
            $query->where('section_id', '=', "{$section_id}");
       }
       
       if ($subsection_id = $filters->get('subsection_id')) {
            $query->where('subsection_id', '=', "{$subsection_id}");
       }
       
       // ricerca per intervent_type_id
        if ($intervent_type_id = $filters->get('intervent_type_id')) {
            $query->where('intervent_type_id', '=', "{$intervent_type_id}");
       } 
        // ricerca per author_id
        if ($author_id = $filters->get('author_id')) {
            $query->where('author_id', '=', "{$author_id}");
       }
       
        // ricerca per created-at from
        if ($createdAtFrom = $filters->get('created-at-from')) {
            $query->where('created_at', '>=', "{$createdAtFrom}");
        }
        // ricerca per created-at from
        if ($createdAtTo = $filters->get('created-at-to')) {
            $query->where('created_at', '<=', "{$createdAtTo}");
        }
         $user = \Auth::user();
        /** restringe il recordset in caso di mancanza di permessi */
         if (!$user->can(PERMISSION_ADMIN)) { 
             // L'utente loggato non e' un admin   
             // SE SI TRATTA DI UN DIPENDENTE  ALLORA MOSTRO SOLO QUELLI LEGATI A project_user
             if ($user->hasRole(ROLE_WORKER)) {
                 $query->Join('projects', 'tasks.project_id', '=', 'projects.id')
                   /* ->where('projects.project_status', '=', PROJECT_STATUS_OPEN) */
                    ->whereExists(function ($q) {
                        $user = \Auth::user();
                        $q->from('project_user')->whereRaw("project_user.user_id = {$user->id}");
                  });
             } 
             
             // RUOLO BOOT MANAGER potrebbe essere questo il ruolo da assegnare all'equipaggio ? da discutere con Danilo
             if ($user->hasRole(ROLE_BOAT_MANAGER)) {
                 // TODO deve vedere solo i task relazionati alla barca 
//                $query->whereHas('author', function($q) use ($user)
//                     {
//                        $q->whereUser_id($user->id);
//                     });
             }
         } 
       
        
        
        
    }
 
    
}
