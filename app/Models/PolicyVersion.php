<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyVersion extends Model
{
    protected $table = 'policy_versions';
    protected $fillable = [
        'type',
        'title',
        'content_html',
        'facility_id',
        'version',
        'is_active',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_policy_version');
    }
}
