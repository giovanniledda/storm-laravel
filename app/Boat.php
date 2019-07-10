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

    public function site()
    {
//        return $this->hasOneThrough('App\Site', 'App\Project');  // forse non funziona perché i progetti sono "many"
    }

    public function users()
    {
        return $this->belongsToMany('App\Users')
            ->using('App\BoatUser')
            ->withPivot([
                'role',
                'created_by',
                'updated_by'
            ]);
    }
}
