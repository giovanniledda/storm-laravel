<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $table = 'project_user';

    protected $fillable = [
        // 'role',
        'profession_id',
        'project_id',
        'user_id'
    ];

}

/*

<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{

}
