<?php

namespace App\JsonApi\V1\Zones;

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
     * Mapping of JSON API filter names to model scopes.
     *
     * @var array
     */
    protected $filterScopes = [];

    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging)
    {
        parent::__construct(new \App\Zone(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        $this->filterWithScopes($query, $filters);
        // ricerca per project_id
//        if ($project_id = $filters->get('project_id')) {
//            $query->where('project_id', '=', "{$project_id}");
//        }
    }


    // Relationships (https://laravel-json-api.readthedocs.io/en/latest/basics/adapters/)

    protected function parent_zone()
    {
        return $this->belongsTo('parent_zone');
    }

    public function children_zones()
    {
        return $this->hasMany('children_zones');
    }
}
