<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_news');
    }
}
