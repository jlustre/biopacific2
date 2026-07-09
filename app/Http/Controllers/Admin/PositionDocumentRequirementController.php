<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\UploadType;
use App\Services\EmployeeDocumentRequirementsService;
use App\Services\PositionDocumentRequirementsSeedService;
use App\Services\PositionDocumentRequirementsSeederExporter;
use App\Services\PositionRequirementPresetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionDocumentRequirementController extends Controller
{
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['add', 'replace', 'remove'])],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'position_group_keys' => ['nullable', 'array'],
            'position_group_keys.*' => ['string'],
            'upload_type_ids' => ['nullable', 'array'],
            'upload_type_ids.*' => ['integer', 'exists:upload_types,id'],
            'document_set_keys' => ['nullable', 'array'],
            'document_set_keys.*' => ['string'],
        ]);

        $presetService = app(PositionRequirementPresetService::class);
        $requirementsService = app(EmployeeDocumentRequirementsService::class);

        $positionIds = collect($validated['position_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->merge($presetService->positionIdsForGroups($validated['position_group_keys'] ?? []))
            ->unique()
            ->values()
            ->all();

        $uploadTypeIds = collect($validated['upload_type_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->merge($presetService->uploadTypeIdsForDocumentSets($validated['document_set_keys'] ?? []))
            ->unique()
            ->values()
            ->all();

        if ($positionIds === []) {
            return back()->with('error', 'Select at least one position or position group.');
        }

        if ($uploadTypeIds === []) {
            return back()->with('error', 'Select at least one document or document preset.');
        }

        $action = $validated['action'];
        $positionCount = count($positionIds);
        $documentCount = count($uploadTypeIds);

        match ($action) {
            'add' => $requirementsService->addRequirementsToPositions($positionIds, $uploadTypeIds),
            'replace' => $requirementsService->replaceRequirementsForPositions($positionIds, $uploadTypeIds),
            'remove' => $requirementsService->removeRequirementsFromPositions($positionIds, $uploadTypeIds),
        };

        $actionLabel = match ($action) {
            'add' => 'added to',
            'replace' => 'set as requirements for',
            'remove' => 'removed from',
        };

        return redirect()
            ->route('admin.upload-types.index', ['tab' => 'requirements', 'department_id' => $request->input('department_id')])
            ->with('success', "{$documentCount} document type(s) {$actionLabel} {$positionCount} position(s).");
    }

    public function applyDefaults(Request $request): RedirectResponse
    {
        if (! \App\Support\MemberPortalLayout::userCanAccessDocumentsManagement($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'only_when_empty' => ['nullable', 'boolean'],
            'include_unmapped' => ['nullable', 'boolean'],
        ]);

        $result = app(PositionDocumentRequirementsSeedService::class)->seed(
            onlyWhenEmpty: (bool) ($validated['only_when_empty'] ?? true),
            includeUnmappedPositions: (bool) ($validated['include_unmapped'] ?? true),
        );

        $message = "Default requirements applied to {$result['positions_processed']} position(s).";
        if ($result['positions_skipped'] > 0) {
            $message .= " {$result['positions_skipped']} position(s) skipped (already had requirements).";
        }

        return redirect()
            ->route('admin.upload-types.index', ['tab' => 'requirements'])
            ->with('success', $message);
    }

    public function syncSeeder(PositionDocumentRequirementsSeederExporter $exporter): RedirectResponse
    {
        if (! $this->canManageSeeder()) {
            return redirect()->route('admin.upload-types.index', ['tab' => 'requirements'])
                ->with('error', 'You do not have permission to update the position requirements seeder.');
        }

        try {
            $result = $exporter->writeSeederFile();

            $message = 'Seeder updated with ' . $result['positions_exported'] . ' position mapping(s). '
                . 'File: database/seeders/data/position_document_requirements.php. '
                . 'Commit it so migrate:fresh --seed restores your assignments.';

            if ($result['custom_sets_added'] > 0) {
                $message .= ' ' . $result['custom_sets_added'] . ' custom set(s) were added for positions that did not match existing presets.';
            }

            return redirect()->route('admin.upload-types.index', ['tab' => 'requirements'])
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.upload-types.index', ['tab' => 'requirements'])
                ->with('error', 'Failed to update seeder: ' . $e->getMessage());
        }
    }

    protected function canManageSeeder(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'super-admin']);
    }
}
