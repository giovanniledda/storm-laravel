<?php

namespace App\JsonApi\V1\Comments;

use App\Models\Comment;
use App\Models\History;
use App\Models\Task;
use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;

class Adapter extends AbstractAdapter
{
    protected $fillable = [
        'body',
        'author_id',
        'commentable_type',  // ex: App\Models\Task
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
        parent::__construct(new \App\Models\Comment(), $paging);
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
            $query->where('commentable_type', '=', \App\Models\Task::class)
                ->where('commentable_id', '=', $task_id);
        }
    }

    protected function creating(Comment $comment, $resource): void
    {
        if ($task_id = isset($resource['task_id']) ? $resource['task_id'] : null) {
            $comment->commentable_type = Task::class;
            $comment->commentable_id = $resource['task_id'];
        }

        if ($task_id = isset($resource['history_id']) ? $resource['history_id'] : null) {
            $comment->commentable_type = History::class;
            $comment->commentable_id = $resource['history_id'];
        }
    }
}
