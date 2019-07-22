<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasStatuses;

    protected $table = 'projects';

    protected $fillable = [
        'name'
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
                'role',
                'created_at',
                'updated_at'
            ]);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            // ... code here
        });

        self::created(function($model){
            // ... code here
            /* dopo aver creato il record pongo il progetto in status aperto */
            $model->setStatus('open');
        });

        self::updating(function($model){
            // ... code here
        });

        self::updated(function($model){
            // ... code here
        });

        self::deleting(function($model){
            // ... code here
        });

        self::deleted(function($model){
            // ... code here
        });
    }
}
