<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPBargainingUnit extends Model
{
    protected $table = 'bp_bargaining_units';
    protected $primaryKey = 'unit_id';
    public $incrementing = true;
    protected $fillable = [
        'unit_name',
        'description',
        'union_code',
        'local_number',
        'contract_name',
        'contract_expiry',
    ];
}
