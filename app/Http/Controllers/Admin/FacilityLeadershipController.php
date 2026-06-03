<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityLeadershipAssignment;
use App\Services\FacilityLeadershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacilityLeadershipController extends Controller
{
    public function __construct(
        protected FacilityLeadershipService $leadership
    ) {}

    public function index()
    {
        $user = Auth::user();
        $facilities = $this->leadership->facilitiesForUser($user);

        if ($facilities->count() === 1) {
            $only = $facilities->first();

            return redirect()->route('admin.facility.leadership.edit', [
                'facility' => $only->getRouteKey(),
            ]);
        }

        $filledCounts = FacilityLeadershipAssignment::query()
            ->whereIn('facility_id', $facilities->pluck('id'))
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->selectRaw('facility_id, COUNT(*) as filled_count')
            ->groupBy('facility_id')
            ->pluck('filled_count', 'facility_id');

        return view('admin.facilities.leadership.index', [
            'facilities' => $facilities,
            'filledCounts' => $filledCounts,
        ]);
    }

    public function edit(Facility $facility)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacility($user, $facility);

        return view('admin.facilities.leadership.edit', [
            'facility' => $facility,
            'rows' => $this->leadership->formRowsForFacility($facility),
            'roleDefinitions' => $this->leadership->roleDefinitions(),
        ]);
    }

    public function update(Request $request, Facility $facility)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacility($user, $facility);

        $validated = $request->validate([
            'leadership' => ['nullable', 'array'],
            'leadership.*' => ['nullable', 'string', 'max:255'],
            'custom_roles' => ['nullable', 'array'],
            'custom_roles.*.id' => ['nullable', 'integer'],
            'custom_roles.*.role_label' => ['nullable', 'string', 'max:120'],
            'custom_roles.*.name' => ['nullable', 'string', 'max:255'],
            'delete_custom_ids' => ['nullable', 'array'],
            'delete_custom_ids.*' => ['integer'],
        ]);

        $this->leadership->syncFacility($facility, $validated);

        return redirect()
            ->route('admin.facility.leadership.edit', ['facility' => $facility->getRouteKey()])
            ->with('success', 'Facility leadership roster saved.');
    }

    public function destroy(Facility $facility, FacilityLeadershipAssignment $assignment)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacility($user, $facility);

        if ((int) $assignment->facility_id !== (int) $facility->id) {
            abort(404);
        }

        if (!$assignment->is_custom) {
            abort(403, 'Standard leadership roles cannot be removed. Clear the name instead.');
        }

        $assignment->delete();

        return redirect()
            ->route('admin.facility.leadership.edit', ['facility' => $facility->getRouteKey()])
            ->with('success', 'Custom leadership role removed.');
    }
}
