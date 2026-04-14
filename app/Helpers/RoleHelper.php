<?php
namespace App\Helpers;

use Spatie\Permission\Models\Role;

class RoleHelper
{
    public static function getAllRolesForSelect()
    {
        return Role::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
