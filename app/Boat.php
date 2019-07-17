<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{

    protected $table = 'boats';
    protected $fillable = [
        'name',
        'registration_number'
    ];

    public function site()
    {
        return $this->hasOne('App\Site');
//        return $this->hasOneThrough('App\Site', 'App\Project');  // NON funziona perché i progetti sono "many" e il site è "one"
    }

    public function sections()
    {
        return $this->hasMany('App\Section');
    }

    public function subsections()
    {
        return $this->hasManyThrough('App\Subsection', 'App\Section');
    }

    public function projects()
    {
        return $this->hasMany('App\Project');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }

    public function associatedUsers() {
        return $this->hasMany('App\BoatUser');
    }

    public function users()
    {
        return $this->belongsToMany('App\User')
            ->using('App\BoatUser')
            ->withPivot([
                'role',
                'created_by',
                'updated_by'
            ]);
    }
}
