<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\Relations\Pivot;


class BoatUser extends Model
// class BoatUser extends Pivot
{
    protected $table = 'boat_user';

    protected $fillable = [
        'profession_id',
        'boat_id',
        'user_id'
    ];

}
/*

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BoatUser extends Pivot
{

}
/**

*/
