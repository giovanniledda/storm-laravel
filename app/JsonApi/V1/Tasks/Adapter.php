<?php

namespace App\JsonApi\V1\Tasks; 
use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
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
        parent::__construct(new \App\Task(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        // TODO
    }
   
     /** @var Model $record */
     public function update($record, array $document, EncodingParametersInterface $parameters)
    { 
        $status = ( isset($document['data']['attributes']['status']) ) ? $document['data']['attributes']['status'] : null;
        $user = \Auth::user();
        $document['data']['attributes']['author_id'] = $user->id;
        // verifico che status sia stato passato e che corrisponda ad un stato valido per il task
        if ($status && in_array($status, TASKS_STATUSES)) {
          $record->setStatus($status); 
        }
        return parent::update($record, $document, $parameters);
    }
}
