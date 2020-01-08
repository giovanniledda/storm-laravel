<?php

namespace App\JsonApi\V1\Tools;

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
        parent::__construct(new \App\Tool(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
//        $this->filterWithScopes($query, $filters);

        if ($name = $filters->get('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($producer = $filters->get('producer')) {
            $query->where('producer', 'like', "%{$producer}%");
        }

        if ($serial_number = $filters->get('serial_number')) {
            $query->where('serial_number', 'like', "%{$serial_number}%");
        }
    }

}
