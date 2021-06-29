<?php

namespace App\JsonApi\V1\Boats;

use App\Models\Boat;
use App\Models\User;
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
        parent::__construct(new \App\Models\Boat(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {

        /** @var User $user */
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
        if (! $user->can(PERMISSION_ADMIN) || ! $user->can(PERMISSION_BACKEND_MANAGER)) {
            // L'utente loggato non e' un admin
            // SE SI TRATTA DI UN DIPENDENTE  ALLORA MOSTRO SOLO QUELLI LEGATI A project_user
            if ($user->hasRole(ROLE_WORKER)) {

                /**
                 *  trovo solo le barche che hanno un progetto a cui l'utente di sessione e' associato  in project_user
                 */

//                $query->whereIn('boats.id', $user->boatsOfMyActiveProjects(true));

                $query->select('boats.*')
                    ->distinct()
                    ->leftJoin('projects', 'boats.id', '=', 'projects.boat_id')
                    ->leftJoin('project_user', 'projects.id', '=', 'project_user.project_id')
                    ->where('project_user.user_id', '=', $user->id);

//                 ->where( function ($q){
//                     $user = \Auth::user();
//                     $q->where('project_user.user_id', '=' , $user->id);})
//                 ->where('projects.project_status', '=', PROJECT_STATUS_IN_SITE);
            }

            // RUOLO BOOT MANAGER potrebbe essere questo il ruolo da assegnare all'equipaggio ? da discutere con Danilo
            if ($user->can(ROLE_BOAT_MANAGER)) {
                $query->whereHas('users', function ($q) use ($user) {
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

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class);
    }

    /**
     * Pre-delete hook
     *
     * @param Boat $boat
     */
    protected function deleting(Boat $boat)
    {
        abort_if($boat->sections()->count(), 412, __(HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG, ['resource' => 'Boat', 'entities' => 'Sections']));
        abort_if($boat->projects()->count(), 412, __(HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG, ['resource' => 'Boat', 'entities' => 'Projects']));
    }
}
