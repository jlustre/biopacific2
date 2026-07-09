<?php

namespace App\Services;

use App\Models\Position;
use App\Models\UploadType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PositionRequirementPresetService
{
    /**
     * @return array<string, array{label: string, description: string, icon?: string, document_count: int, document_names: list<string>}>
     */
    public function documentSetCatalog(): array
    {
        $seederConfig = require database_path('seeders/data/position_document_requirements.php');
        $sets = $seederConfig['sets'] ?? [];
        $presets = config('position_requirement_presets.document_sets', []);
        $typesByName = UploadType::query()
            ->generalPositionAssignable()
            ->pluck('id', 'name');

        $catalog = [];

        foreach ($presets as $key => $meta) {
            $names = collect($sets[$key] ?? [])
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->values()
                ->all();

            $resolvedNames = collect($names)
                ->filter(fn (string $name) => $typesByName->has($name))
                ->values()
                ->all();

            $catalog[$key] = [
                'label' => (string) ($meta['label'] ?? $key),
                'description' => (string) ($meta['description'] ?? ''),
                'icon' => (string) ($meta['icon'] ?? 'fa-file'),
                'document_count' => count($resolvedNames),
                'document_names' => $resolvedNames,
            ];
        }

        return $catalog;
    }

    /**
     * @return array<string, array{label: string, description: string, position_count: int, position_titles: list<string>}>
     */
    public function positionGroupCatalog(): array
    {
        $presets = config('position_requirement_presets.position_groups', []);
        $catalog = [];

        foreach ($presets as $key => $meta) {
            $positions = $this->resolvePositionsForGroup($key);
            $catalog[$key] = [
                'label' => (string) ($meta['label'] ?? $key),
                'description' => (string) ($meta['description'] ?? ''),
                'position_count' => $positions->count(),
                'position_titles' => $positions->pluck('title')->values()->all(),
            ];
        }

        return $catalog;
    }

    /**
     * @return list<int>
     */
    public function uploadTypeIdsForDocumentSets(array $setKeys): array
    {
        $seederConfig = require database_path('seeders/data/position_document_requirements.php');
        $sets = $seederConfig['sets'] ?? [];

        $typeNames = collect($setKeys)
            ->flatMap(fn ($key) => $sets[$key] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($typeNames->isEmpty()) {
            return [];
        }

        return UploadType::query()
            ->generalPositionAssignable()
            ->whereIn('name', $typeNames->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public function positionIdsForGroups(array $groupKeys): array
    {
        return collect($groupKeys)
            ->flatMap(fn (string $key) => $this->resolvePositionsForGroup($key))
            ->unique('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Position>
     */
    public function resolvePositionsForGroup(string $groupKey): Collection
    {
        $presets = config('position_requirement_presets.position_groups', []);
        $meta = $presets[$groupKey] ?? null;

        if (! is_array($meta)) {
            return collect();
        }

        $query = Position::query()
            ->with('department')
            ->where('is_active', true)
            ->orderBy('title');

        if ($groupKey === 'all_active' || ! empty($meta['all_active'])) {
            return $query->get();
        }

        if (! empty($meta['department_names']) && is_array($meta['department_names'])) {
            $names = $meta['department_names'];

            return $query->whereHas('department', function ($departmentQuery) use ($names) {
                $departmentQuery->whereIn('name', $names);
            })->get();
        }

        if (! empty($meta['position_titles']) && is_array($meta['position_titles'])) {
            return $query->whereIn('title', $meta['position_titles'])->get();
        }

        return collect();
    }

    /**
     * Paginated summary rows for the requirements overview table.
     */
    public function paginatePositionRequirementOverview(
        ?int $departmentId = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $requirements = app(EmployeeDocumentRequirementsService::class);

        $query = Position::query()
            ->with(['department', 'requiredUploadTypes' => fn ($relation) => $relation->generalPositionAssignable()->orderBy('name')])
            ->where('is_active', true)
            ->when($departmentId, fn ($builder) => $builder->where('department_id', $departmentId))
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($scope) use ($search) {
                    $scope->where('title', 'like', '%' . $search . '%')
                        ->orWhere('position_code', 'like', '%' . $search . '%')
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('requiredUploadTypes', function ($uploadTypeQuery) use ($search) {
                            $uploadTypeQuery->generalPositionAssignable()
                                ->where('upload_types.name', 'like', '%' . $search . '%')
                                ->where('position_upload_type_requirements.is_required', true);
                        });
                });
            })
            ->orderBy('title');

        return $query->paginate($perPage)->withQueryString()->through(function (Position $position) use ($requirements) {
            $documents = $requirements->requiredGeneralTypesSummaryForPosition($position);

            return [
                'position_id' => (int) $position->id,
                'title' => $position->title,
                'department' => $position->department?->name,
                'requirement_count' => count($documents),
                'documents' => $documents,
            ];
        });
    }
}
