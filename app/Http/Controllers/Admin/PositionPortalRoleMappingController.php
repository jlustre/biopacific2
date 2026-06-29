<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\PositionPortalRoleMapping;
use App\Support\PositionPortalRoleMappingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionPortalRoleMappingController extends Controller
{
    public function __construct(
        protected PositionPortalRoleMappingService $mappingService,
    ) {}

    public function index(Request $request)
    {
        $query = PositionPortalRoleMapping::query()->with(['position.department']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('position', function ($positionQuery) use ($search) {
                $positionQuery->where('title', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role_name', $request->string('role')->toString());
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        $mappings = $query
            ->join('positions', 'positions.id', '=', 'position_portal_role_mappings.position_id')
            ->orderBy('positions.title')
            ->select('position_portal_role_mappings.*')
            ->paginate(20)
            ->withQueryString();

        $roles = $this->mappingService->assignableRoles();

        return view('admin.position-portal-roles.index', compact('mappings', 'roles'));
    }

    public function create()
    {
        $roles = $this->mappingService->assignableRoles();
        $mappedPositionIds = PositionPortalRoleMapping::query()->pluck('position_id');
        $positions = Position::query()
            ->with('department')
            ->whereNotIn('id', $mappedPositionIds)
            ->orderBy('title')
            ->get();

        return view('admin.position-portal-roles.create', compact('roles', 'positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'position_id' => [
                'required',
                'integer',
                'exists:positions,id',
                Rule::unique('position_portal_role_mappings', 'position_id'),
            ],
            'role_name' => $this->roleNameRules(),
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        PositionPortalRoleMapping::create($validated);

        return redirect()
            ->route('admin.position-portal-roles.index')
            ->with('success', 'Position role mapping created successfully.');
    }

    public function edit(PositionPortalRoleMapping $mapping)
    {
        $mapping->load('position.department');
        $roles = $this->mappingService->assignableRoles();

        return view('admin.position-portal-roles.edit', [
            'mapping' => $mapping,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, PositionPortalRoleMapping $mapping): RedirectResponse
    {
        $validated = $request->validate([
            'role_name' => $this->roleNameRules(),
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $mapping->update($validated);

        return redirect()
            ->route('admin.position-portal-roles.index')
            ->with('success', 'Position role mapping updated successfully.');
    }

    public function destroy(PositionPortalRoleMapping $mapping): RedirectResponse
    {
        $mapping->delete();

        return redirect()
            ->route('admin.position-portal-roles.index')
            ->with('success', 'Position role mapping removed.');
    }

    public function syncDefaults(): RedirectResponse
    {
        $count = $this->mappingService->syncDefaultMappings();

        return redirect()
            ->route('admin.position-portal-roles.index')
            ->with('success', "Synced {$count} default position role mapping(s) from leadership configuration.");
    }

    /**
     * @return list<\Illuminate\Validation\Rules\Exists|\Illuminate\Validation\Rules\In|string>
     */
    private function roleNameRules(): array
    {
        return [
            'required',
            'string',
            Rule::in($this->mappingService->assignableRoleNames()),
            Rule::exists('roles', 'name'),
        ];
    }
}
