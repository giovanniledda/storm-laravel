<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BoatUser extends Pivot
{
    protected $table = 'boat_user';

    public $incrementing = true;

    protected $fillable = [
        'profession_id',
        'boat_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function boat()
    {
        return $this->belongsTo(\App\Models\Boat::class);
    }
}
