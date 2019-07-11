<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Lecturize\Addresses\Traits\HasAddresses;

class Site extends Model
{
    use HasAddresses;

    protected $table = 'sites';

    protected $fillable = [
        'name'
    ];

    public function projects()
    {
        return $this->hasMany('App\Project');
    }
}
