<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoatUser extends Model
{
    protected $table = 'boat_user';

    protected $fillable = [
        'role',
        'boat_id',
        'user_id'
    ];

}
