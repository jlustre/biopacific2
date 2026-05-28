<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class FixChecklistUserNamesController
{
    public static function getAllUsersForChecklist()
    {
        // You may want to filter users by role or status if needed
        return User::select('id', 'name')->get();
    }
}
