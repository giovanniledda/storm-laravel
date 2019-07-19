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


         /** restringe il recordset in caso di mancanza di permessi */
         if (!$user->can('Admin')) {
            $query->whereHas('users', function($q) use ($user)
            {
             $q->whereUser_id($user->id);
            });
        }

    }

}
