<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpDocument extends Model
{
    protected $table = 'bp_emp_documents';

    protected $primaryKey = 'document_id';

    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'effdt',
        'effseq',
        'comments',
    ];

    protected $casts = [
        'effdt' => 'date',
        'effseq' => 'integer',
        'file_size' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }
}
