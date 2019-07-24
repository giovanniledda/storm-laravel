<?php

namespace App\JsonApi\V1\Comments;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Adapter extends AbstractAdapter
{
    protected $fillable = [
        'body',
        'author_id',
        'commentable_type',  // ex: App\Task
        'commentable_id',    // ex: 1
    ];

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
        parent::__construct(new \App\Comment(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        // Restituisce i commenti di un Task
        if ($task_id = $filters->get('task_id')) {
            $query->where('commentable_type', '=', 'App\\Task')
                ->where('commentable_id', '=', $task_id);
        }
    }

}
