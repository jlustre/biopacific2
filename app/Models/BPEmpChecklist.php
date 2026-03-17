<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BPEmpChecklist extends Model
{
    use HasFactory;
    protected $table = 'bp_emp_checklists';
    protected $fillable = [
        'emp_id',
        'items',
    ];
    protected $casts = [
        'items' => 'array',
    ];
}
