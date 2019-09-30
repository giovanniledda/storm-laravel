<?php

namespace App\JsonApi\V1\Updates;

use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use const PERMISSION_BOAT_MANAGER;

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
        parent::__construct(new \App\Update(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        $user = \Auth::user();

        /** restringe il recordset in caso di mancanza di permessi */
        if (1 || $user->can(PERMISSION_BOAT_MANAGER)) {  // per ora bypassato
            $query->where('notifiable_type', '=','App\\User')
                ->where('notifiable_id', $user->id);
        }
        
         if ($author_id = $filters->get('author_id')) {
             $query->where('notifications.data', 'regexp' , '"action_author_id":"[[:<:]]'.$author_id.'[[:>:]]"');
         }
         
         if ($boat_id = $filters->get('boat_id')) {
             $query->where('notifications.data', 'regexp' , '"boat_id":"[[:<:]]'.$boat_id.'[[:>:]]"');
         }
        
        //SELECT * FROM `notifications` where data REGEXP '"author_id":"[[:<:]]2[[:>:]]"'
//        
//        if ($site_id = $filters->get('site_id')) {
//            $query->where('boats.site_id', '=', "{$site_id}");
//        }
        
    }

}
