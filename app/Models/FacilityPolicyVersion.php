<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityPolicyVersion extends Model
{
    protected $table = 'facility_policy_version';
    protected $fillable = [
        'facility_id',
        'policy_version_id',
    ];
}
