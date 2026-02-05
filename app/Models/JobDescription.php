<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobDescription extends Model
{
    protected $fillable = ['title', 'description', 'position_id', 'version'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
