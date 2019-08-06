<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Venturecraft\Revisionable\RevisionableTrait;
use Log;

class Project extends Model
{
    use HasStatuses, RevisionableTrait;

    protected $table = 'projects';

    protected $fillable = [
       'name', 'project_status', 'boat_id', 'project_type'
    ];
  
    
    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function site()
    {
        return $this->belongsTo('App\Site');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    public function history()
    {
        return $this->hasMany('App\ProjectHistory');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }


    public function users()
    {
        return $this->belongsToMany('App\User')
            ->using('App\ProjectUser')
            ->withPivot([
                // 'role',
                'profession_id',
                'created_at',
                'updated_at'
            ]);
    }
     
}
