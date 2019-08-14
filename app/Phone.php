<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $table = 'users_tel';

    protected $fillable = [
        'phone_number',
        'phone_type',
        'user_id',
    ];
}