<?php
namespace App;


use Illuminate\Database\Eloquent\Relations\Pivot;


class BoatUser extends Pivot
{
    protected $table = 'boat_user'; 

    public $incrementing = true;

    protected $fillable = [
        'profession_id',
        'boat_id',
        'user_id'
    ];

} 