<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentContact extends Model
{
    protected $fillable = [
        'role', 'name', 'title', 'email', 'phone'
    ];
}
