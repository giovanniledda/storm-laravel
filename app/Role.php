<?php

namespace App;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public static function defaultRoles()
    {
        return \Config::get('roles.default');
    }
}
