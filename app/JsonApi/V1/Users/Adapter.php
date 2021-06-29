<?php

namespace App\JsonApi\V1\Users;

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

    protected $fillable = [
        'name', 'surname', 'email', 'password', 'is_storm', 'disable_login',
    ];

    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging)
    {
        parent::__construct(new \App\Models\User(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        // TODO implementare un filtro per boat_id e per project_id

        if ($boat_id = $filters->get('boat_id')) {
            $query->Join('boat_user', 'users.id', '=', 'boat_user.user_id')->where('boat_user.user_id', '=', $boat_id);
        }

        if ($project_id = $filters->get('project_id')) {
            $query->Join('project_user', 'users.id', '=', 'project_user.user_id')->where('project_user.project_id', '=', $project_id);
        }

        if ($is_storm = $filters->get('is_storm')) {
            $query->where('is_storm', '=', $is_storm);
        }

        // $query->Join('project_user', 'user.id', '=', 'project_user.user_id');
    }
}
