<?php

namespace App\Services;

use App\Models\Position;
use App\Models\UploadType;
use Illuminate\Support\Collection;

class PositionDocumentRequirementsSeedService
{
    public function __construct(
        protected EmployeeDocumentRequirementsService $requirements
    ) {
    }

    /**
     * @return array{
     *     positions_processed: int,
     *     positions_skipped: int,
     *     positions_missing: list<string>,
     *     types_missing: list<string>,
     *     requirements_synced: int
     * }
     */
    public function seed(bool $onlyWhenEmpty = true, bool $includeUnmappedPositions = true): array
    {
        $config = require database_path('seeders/data/position_document_requirements.php');
        $sets = $config['sets'] ?? [];
        $positionSets = $config['position_sets'] ?? [];

        $typesByName = UploadType::query()
            ->generalPositionAssignable()
            ->pluck('id', 'name');

        $positionsProcessed = 0;
        $positionsSkipped = 0;
        $positionsMissing = [];
        $typesMissing = [];
        $requirementsSynced = 0;

        $defaultSetNames = ['all_staff'];

        $positions = Position::query()->orderBy('title')->get();

        foreach ($positions as $position) {
            $setNames = $positionSets[$position->title] ?? ($includeUnmappedPositions ? $defaultSetNames : null);

            if ($setNames === null) {
                continue;
            }

            if ($onlyWhenEmpty && $this->positionHasRequirements($position)) {
                $positionsSkipped++;
                continue;
            }

            $typeNames = $this->resolveTypeNamesForSets($sets, $setNames);
            $uploadTypeIds = [];

            foreach ($typeNames as $typeName) {
                $typeId = $typesByName[$typeName] ?? null;
                if (!$typeId) {
                    $typesMissing[] = $typeName;
                    continue;
                }
                $uploadTypeIds[] = (int) $typeId;
            }

            $uploadTypeIds = array_values(array_unique($uploadTypeIds));

            if ($uploadTypeIds === []) {
                $positionsMissing[] = $position->title;
                continue;
            }

            $this->requirements->syncPositionRequirements($position, $uploadTypeIds);
            $positionsProcessed++;
            $requirementsSynced += count($uploadTypeIds);
        }

        return [
            'positions_processed' => $positionsProcessed,
            'positions_skipped' => $positionsSkipped,
            'positions_missing' => array_values(array_unique($positionsMissing)),
            'types_missing' => array_values(array_unique($typesMissing)),
            'requirements_synced' => $requirementsSynced,
        ];
    }

    /**
     * @param  array<string, list<string>>  $sets
     * @param  list<string>  $setNames
     * @return list<string>
     */
    protected function resolveTypeNamesForSets(array $sets, array $setNames): array
    {
        return collect($setNames)
            ->flatMap(fn (string $setName) => $sets[$setName] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    protected function positionHasRequirements(Position $position): bool
    {
        return $position->requiredUploadTypes()
            ->wherePivot('is_required', true)
            ->whereNull('upload_types.checklist_item_id')
            ->exists();
    }

    /**
     * @return Collection<int, string>
     */
    public function definedPositionTitles(): Collection
    {
        $config = require database_path('seeders/data/position_document_requirements.php');

        return collect(array_keys($config['position_sets'] ?? []));
    }
}
