<?php

namespace App\JsonApi\V1\Projects;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Adapter extends AbstractAdapter
{

    protected $fillable = ['name'];

    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * @var array
     */
    protected $attributes = [];

      /**
     * @inheritdoc
     */
    protected $relationships = [
        'tasks','boat'
    ];
    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging)
    {
        parent::__construct(new \App\Project(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        /** implementa la ricerca per name */
        if ($this->status = $filters->get('status')) {
            $query->whereIn(
                'id',
                function (\Illuminate\Database\Query\Builder $query) {
                    $query
                        ->select(\Illuminate\Support\Facades\DB::raw('max(id)'))
                        ->from('statuses') // todo: get table name from somehwere
                        ->where('model_type', 'App\Project')
                        ->where('name', $this->status)
                        ;
                }
            );
        }

    }

    /**
     * @return HasMany
     */

    protected function tasks() {
        return $this->hasMany();
    }

      /**
     * @return BelongsTo
     */
    protected function boat()
    {
        return $this->belongsTo();
    }
}
