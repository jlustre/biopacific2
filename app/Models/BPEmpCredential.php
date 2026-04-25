<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpCredential extends Model
{
    protected $table = 'bp_emp_credentials';
    protected $primaryKey = 'credential_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'credential_type',
        'credential_number',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'verified_via',
        'last_verified_dt',
        'status',
    ];
}
