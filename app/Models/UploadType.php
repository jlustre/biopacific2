<?php

namespace App\Models;

use App\Services\ChecklistUploadTypeSyncService;
use App\Services\EmployeeDocumentRequirementsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'requires_expiry',
        'is_license_or_certification',
        'department_ids',
        'checklist_item_id',
        'checklist_section',
        'doc_type_id',
        'sort_order',
        'applies_to_all_positions',
    ];

    protected $casts = [
        'requires_expiry' => 'boolean',
        'is_license_or_certification' => 'boolean',
        'applies_to_all_positions' => 'boolean',
        'department_ids' => 'array',
        'sort_order' => 'integer',
    ];

    public $timestamps = false;

    protected static bool $syncingChecklistProjection = false;

    protected static function booted(): void
    {
        static::saved(function (self $uploadType): void {
            if (static::$syncingChecklistProjection) {
                return;
            }

            if (! in_array((string) $uploadType->checklist_section, ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS, true)) {
                return;
            }

            if (! $uploadType->wasRecentlyCreated && ! $uploadType->wasChanged([
                'name',
                'requires_expiry',
                'is_license_or_certification',
                'checklist_section',
                'doc_type_id',
                'sort_order',
                'applies_to_all_positions',
            ])) {
                return;
            }

            static::$syncingChecklistProjection = true;
            try {
                $fresh = static::withoutGlobalScopes()->find($uploadType->id);
                if ($fresh) {
                    app(ChecklistUploadTypeSyncService::class)->syncUploadType($fresh);
                }
            } finally {
                static::$syncingChecklistProjection = false;
            }
        });
    }

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

    public function docType()
    {
        return $this->belongsTo(DocType::class, 'doc_type_id');
    }

    public function isEmployeeFileChecklistType(): bool
    {
        return $this->checklist_item_id !== null
            || in_array((string) $this->checklist_section, ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS, true);
    }

    public function scopeGeneralDocumentTypes(Builder $query): Builder
    {
        return $query->whereNull('checklist_section');
    }

    /**
     * @deprecated Use catalogPositionAssignable — all catalog types can be assigned to positions.
     */
    public function scopeGeneralPositionAssignable(Builder $query): Builder
    {
        return $query->catalogPositionAssignable();
    }

    public function scopeCatalogPositionAssignable(Builder $query): Builder
    {
        return $query;
    }

    public function scopeEmployeeFileSections(Builder $query): Builder
    {
        return $query->whereIn(
            'checklist_section',
            ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS
        );
    }

    public function scopeApplicableToPosition(Builder $query, ?int $positionId): Builder
    {
        if (! $positionId) {
            return $query->where('applies_to_all_positions', true);
        }

        return $query->where(function (Builder $scope) use ($positionId) {
            $scope->where('applies_to_all_positions', true)
                ->orWhereHas('positions', function (Builder $positions) use ($positionId) {
                    $positions->where('positions.id', $positionId)
                        ->where('position_upload_type_requirements.is_required', true);
                });
        });
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
        )->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Collection of UploadType models for an employee (position-aware).
     */
    public static function catalogForEmployee(?BPEmployee $employee): \Illuminate\Support\Collection
    {
        return app(EmployeeDocumentRequirementsService::class)->catalogUploadTypesForEmployee($employee);
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
