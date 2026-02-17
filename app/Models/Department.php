<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'type', 'description'];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
