<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'section',
        'doc_type_id',
        'order',
    ];

    public function docType()
    {
        return $this->belongsTo(DocType::class, 'doc_type_id');
    }
}
