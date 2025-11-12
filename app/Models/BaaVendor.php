<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaaVendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_service',
        'type',
        'ephi_access',
        'baa_status',
        'notes',
        'baa_form_path',
        'facility_id',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
