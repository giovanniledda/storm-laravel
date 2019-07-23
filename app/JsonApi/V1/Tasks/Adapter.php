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
        'number', 'title', 'status','description','estimated_hours','worked_hours',
        'for_admins', 'project_id', 'section_id', 'intervent_type_id','author_id','subsection_id'
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
        parent::__construct(new \App\Task(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        
       // ricerca per status
       if ($status = $filters->get('status')) {
            $query->where('status', '=', "{$status}");
       } 
       // ricerca per project_id
       if ($project_id = $filters->get('project_id')) {
            $query->where('project_id', '=', "{$project_id}");
       }
       
       // ricerca per section_id
       if ($section_id = $filters->get('section_id')) {
            $query->where('section_id', '=', "{$section_id}");
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
       
    }

    /** @var Model $record */
    public function update($record, array $document, EncodingParametersInterface $parameters)
    {
        $status = (isset($document['data']['attributes']['status'])) ? $document['data']['attributes']['status'] : null;
        $user = \Auth::user();
        $document['data']['attributes']['author_id'] = $user->id;
        // verifico che status sia stato passato e che corrisponda ad un stato valido per il task
        if ($status && in_array($status, TASKS_STATUSES)) {
            $record->setStatus($status);
        }
        return parent::update($record, $document, $parameters);
    }
}
