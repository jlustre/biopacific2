<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityLegal extends Model
{
    protected $table = 'facility_legals';
    protected $fillable = [
        'facility_id',
        'legal_id',
    ];
}
