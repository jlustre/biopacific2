<?php

namespace App\Models;

use App\Services\ChecklistUploadTypeSyncService;
use App\Services\EmployeeDocumentRequirementsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'requires_expiry',
        'is_license_or_certification',
        'department_ids',
        'checklist_item_id',
        'checklist_section',
    ];

    protected $casts = [
        'requires_expiry' => 'boolean',
        'is_license_or_certification' => 'boolean',
        'department_ids' => 'array',
    ];

    public $timestamps = false;

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function positionRequirements()
    {
        return $this->hasMany(PositionUploadTypeRequirement::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_upload_type_requirements')
            ->withPivot(['is_required'])
            ->withTimestamps();
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    public function isEmployeeFileChecklistType(): bool
    {
        return $this->checklist_item_id !== null;
    }

    public function scopeGeneralDocumentTypes(Builder $query): Builder
    {
        return $query->whereNull('checklist_section');
    }

    /**
     * Types assignable via position required-documents UI (excludes checklist-synced types).
     */
    public function scopeGeneralPositionAssignable(Builder $query): Builder
    {
        return $query->whereNull('checklist_item_id');
    }

    public function scopeEmployeeFileSections(Builder $query): Builder
    {
        return $query->whereIn(
            'checklist_section',
            ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS
        );
    }

    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        return $query->orderByRaw(
            "CASE checklist_section
                WHEN 'PART A' THEN 1
                WHEN 'PART B' THEN 2
                WHEN 'PART C' THEN 3
                WHEN 'PART D' THEN 4
                ELSE 5
            END"
        )->orderBy('name');
    }

    /**
     * @return list<array{id: int, name: string, requires_expiry: bool, section: string, kind: string}>
     */
    public static function optionsForEmployee(?BPEmployee $employee, array $documentComplianceItems = []): array
    {
        return app(EmployeeDocumentRequirementsService::class)
            ->uploadOptionsForEmployee($employee, $documentComplianceItems);
    }
}
