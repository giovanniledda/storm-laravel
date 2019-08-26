<?php

namespace App\JsonApi\V1\Projects;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Adapter extends AbstractAdapter
{

    protected $fillable = ['name', 'status', 'boat_id', 'project_type', 'project_progress','site_id'];

    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * @var array
     */
    
    // mappa il nome della proprieta della risorsa API con il nome del campo nel database
     protected $attributes = ['status'=> 'project_status'];
 
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
        /*
        if ($status = $filters->get('status')) {
            $query->where('project_status', '=', "{$status}");
        }
        if ($boat_id = $filters->get('boat_id')) {
            $query->where('boat_id', '=', "{$boat_id}");
        }
        
        $user = \Auth::user();
        if (!$user->can(PERMISSION_ADMIN) || !$user->can(PERMISSION_BACKEND_MANAGER)) { 
            if ($user->hasRole(ROLE_WORKER)) {
                 $query->Join('project_user', 'projects.id', '=', 'project_user.project_id')->where('project_user.user_id', '=', $user->id) ;
             } 
              if ($user->can(PERMISSION_BOAT_MANAGER)) { 
                $query->Join('boat_user', 'projects.boat_id', '=', 'boat_user.boat_id')  
                        ->where('boat_user.user_id', '=', $user->id) ;
                
             }    
        } 
        */
        
        
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

    /**** RELAZIONI PER LE RISORSE **/

      protected function users() {
        return $this->hasMany();
    }
    
    
    
    protected function tasks() {
        return $this->hasMany();
    }

    protected function sections() {
        return $this->hasMany();
    }
    /**
     * @return BelongsTo
     */
    protected function boat()
    {
        return $this->belongsTo();
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

    
}
