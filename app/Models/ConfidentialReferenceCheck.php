<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfidentialReferenceCheck extends Model
{
    use HasFactory;

    protected $table = 'reference_checks';

    protected $fillable = [
        'user_id',
        'reference_index',
        'reference_name',
        'reference_title',
        'company_address',
        'comments',
        'reference_phone',
        'reference_email',
        'company',
        'signed',
        'signed_date',
        'employment_from',
        'employment_to',
        'salary',
        'salary_per',
        'duties_description',
        'performance_description',
        'date_contacted',
        'applicant_signature',
        'signature_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
