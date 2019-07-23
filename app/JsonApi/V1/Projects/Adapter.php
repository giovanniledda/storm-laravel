<?php

namespace App\JsonApi\V1\Projects;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
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
        if ($status = $filters->get('status')) {
            $query->where('status', '=', "{$status}");
        }
        if ($boat_id = $filters->get('boat_id')) {
            $query->where('boat_id', '=', "{$boat_id}");
        }
        /** implementa la ricerca per name non cancellare ma commentare */
        /*if ($this->status = $filters->get('status')) {
            $query->whereIn(
                'id',
                function (\Illuminate\Database\Query\Builder $query) {
                    $query
                        // ->select(\Illuminate\Support\Facades\DB::raw('max(id)'))
                        ->select('model_id')
                        // ->from(\App\Project::getStatusTableName) // 'statuses') // todo: get table name from somehwere
                        ->from('statuses')
                        ->where('model_type', 'App\\Project')
                        ->where('name', $this->status)

                        ->whereIn(
                            'id',
                            function (\Illuminate\Database\Query\Builder $query) {
                                $query
                                    ->select(\Illuminate\Support\Facades\DB::raw('max(id)'))
                                    ->from('statuses')
                                    ->where('model_type', 'App\\Project')
                                    // ->where('name', $this->status)
                                    ->groupBy('model_id')
                                    ;
                                }
                            )

                        ;
                }
            );
        }
        */

    }


//      /** @var Model $record */
//     public function update($record, array $document, EncodingParametersInterface $parameters)
//    {
//        $status = ( isset($document['data']['attributes']['status']) ) ? $document['data']['attributes']['status'] : null;
//
//        // verifico che status sia stato passato e che corrisponda ad un stato valido per il task
//        if ($status && in_array($status, PROJECT_STATUSES)) {
//          $record->setStatus($status);
//        }
//        return parent::update($record, $document, $parameters);
//    }

    
    /**** RELAZIONI PER LE RISORSE **/

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
