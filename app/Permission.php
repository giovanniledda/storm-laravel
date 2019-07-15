<?php

namespace App;

use Spatie\Permission\Models\Permission as SpatiePermission;


class Permission extends SpatiePermission
{
    public static function defaultPermissions()
    {
        return \Config::get('permissions');
    }
}
