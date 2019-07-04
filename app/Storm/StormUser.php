<?php

namespace App\Storm;

use App\User;
use Illuminate\Database\Eloquent\Model;
use NorseBlue\Parentity\Traits\IsMtiChildModel;

class StormUser extends Model
{
    use IsMtiChildModel;

    protected $table = 'storm_users';

    protected $parentModel = User::class;

    protected $parentEntity = 'entity';

    protected $fillable = [
        'nickname',
    ];

}
