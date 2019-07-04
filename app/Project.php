<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use NorseBlue\Parentity\Traits\IsMtiParentModel;

class Project extends Model
{
    use HasStatuses, IsMtiParentModel;

    protected $table = 'projects';

    protected $fillable = [
        'name'
    ];

    /** @optional */
    protected $ownAttributes = [
        'id',
        'name',
        'start_date',
        'end_date',
        'entity_type',
        'entity_id',
    ];

    /**
     * Get all of the models that own projects.
     */
    public function projectable()
    {
        return $this->morphTo();
    }


    public function item()
    {
        return $this->morphOne('App\Item', 'itemable');
    }


    public function site()
    {
        return $this->morphOne('App\Site', 'siteable');
    }


    public function tasks()
    {
        return $this->hasMany('App\Task');
    }


    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }
}
