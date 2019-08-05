<?php

namespace App\JsonApi\V1\Boats;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Adapter extends AbstractAdapter
{

    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * @var array
     */
    protected $attributes = [];
    
    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging)
    {
        parent::__construct(new \App\Boat(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {

         $user = \Auth::user();
         /** implementa la ricerca per site_id */
         if ($site_id = $filters->get('site_id')) {
            $query->where('boats.site_id', '=', "{$site_id}");
         }
         /** implementa la ricerca per name */
         if ($name = $filters->get('name')) {
            $query->where('boats.name', 'like', "{$name}%");
         }
         /** implementa la ricerca per boat_type */
         if ($boat_type = $filters->get('boat_type')) {
            $query->where('boats.boat_type', '=', "{$boat_type}");
         }


         /** restringe il recordset in caso di mancanza di permessi */
         if (!$user->can(PERMISSION_ADMIN) || !$user->can(PERMISSION_BACKEND_MANAGER)) { 
             // L'utente loggato non e' un admin   
             // SE SI TRATTA DI UN DIPENDENTE  ALLORA MOSTRO SOLO QUELLI LEGATI A project_user
             if ($user->hasRole(ROLE_WORKER)) {
                 $query->Join('projects', 'boats.id', '=', 'projects.boat_id')->where('projects.project_status', '=', PROJECT_STATUS_OPEN)
                    ->whereExists(function ($q) {
                        $user = \Auth::user();
                        $q->from('project_user')->whereRaw("project_user.user_id = {$user->id}");
                  });
             } 
             
             // RUOLO BOOT MANAGER potrebbe essere questo il ruolo da assegnare all'equipaggio ? da discutere con Danilo
             if ($user->can(ROLE_BOAT_MANAGER)) {
                $query->whereHas('users', function($q) use ($user)
                     {
                        $q->whereUser_id($user->id);
                     });
             }    
         } 

    }
     
    
    /* RELAZIONI PER LE RISORSE*/
    public function sections()
    {
        return $this->hasMany();
    }
    
    
}
