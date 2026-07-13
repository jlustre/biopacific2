<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityLeadershipAssignment;
use App\Services\FacilityLeadershipService;
use App\Support\SelectedFacility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacilityLeadershipController extends Controller
{
    use ProvidesMemberPortalContext;

    public function __construct(
        protected FacilityLeadershipService $leadership
    ) {}

    public function index()
    {
        $user = Auth::user();
        $facilities = $this->leadership->viewableFacilitiesForUser($user);

        if ($facilities->isEmpty()) {
            return view('admin.facilities.leadership.edit', $this->portalViewData($user, null, $facilities, false));
        }

        $preferredId = SelectedFacility::id();
        $scoped = $this->leadership->writableFacilitiesForUser($user);
        if ($scoped->isEmpty()) {
            $scoped = $this->leadership->scopedHomeFacilities($user);
        }

        $target = $preferredId
            ? $facilities->first(fn (Facility $facility) => (int) $facility->id === (int) $preferredId)
            : null;

        if (! $target && $scoped->isNotEmpty()) {
            $target = $facilities->first(
                fn (Facility $facility) => $scoped->contains(fn (Facility $home) => (int) $home->id === (int) $facility->id)
            );
        }

        $target ??= $facilities->first();

        return redirect()->route('admin.facility.leadership.edit', [
            'facility' => $target->getRouteKey(),
        ]);
    }

    public function edit(Facility $facility)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacility($user, $facility);

        // Only persist selection when the facility is within the user's home/write scope.
        if ($this->leadership->userCanEditLeadership($user, $facility)
            || $this->leadership->scopedHomeFacilities($user)->contains(
                fn (Facility $home) => (int) $home->id === (int) $facility->id
            )) {
            SelectedFacility::remember($facility);
        }

        $facilities = $this->leadership->viewableFacilitiesForUser($user);
        $canEdit = $this->leadership->userCanEditLeadership($user, $facility);

        return view('admin.facilities.leadership.edit', $this->portalViewData(
            $user,
            $facility,
            $facilities,
            $canEdit
        ));
    }

    public function update(Request $request, Facility $facility)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacilityWrite($user, $facility);

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

    public function destroyRole(Facility $facility, string $roleKey)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacilityWrite($user, $facility);
        $this->leadership->authorizeRoleRemoval($user);
        $this->leadership->removeStandardRole($facility, $roleKey);

        return redirect()
            ->route('admin.facility.leadership.edit', ['facility' => $facility->getRouteKey()])
            ->with('success', 'Leadership role removed for this facility.');
    }

    public function destroy(Facility $facility, FacilityLeadershipAssignment $assignment)
    {
        $user = Auth::user();
        $this->leadership->authorizeFacilityWrite($user, $facility);
        $this->leadership->authorizeRoleRemoval($user);

        if ((int) $assignment->facility_id !== (int) $facility->id) {
            abort(404);
        }

        if (! $assignment->is_custom) {
            abort(403, 'Standard leadership roles cannot be removed. Clear the name instead.');
        }

        $assignment->delete();

        return redirect()
            ->route('admin.facility.leadership.edit', ['facility' => $facility->getRouteKey()])
            ->with('success', 'Custom leadership role removed.');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Facility>  $facilities
     * @return array<string, mixed>
     */
    protected function portalViewData($user, ?Facility $facility, $facilities, bool $canEdit): array
    {
        $context = $this->memberPortalContext($user);

        return array_merge($context, [
            'portalNav' => \App\Support\MemberPortalLayout::navModeForCurrentRequest(),
            'portalActive' => 'facility-leadership',
            'portalTitle' => 'Facility Leadership | Bio Pacific',
            'portalEyebrow' => 'Facility Leadership',
            'portalPageTitle' => 'Facility Leadership',
            'portalSubtitle' => match (\App\Support\MemberPortalLayout::navModeForCurrentRequest()) {
                'admin' => 'Bio-Pacific Administration',
                'corporate' => 'Corporate Management',
                'facility' => 'Facility Portal',
                default => 'HR Employee Portal',
            },
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'showPortalFooter' => false,
            'facility' => $facility,
            'facilities' => $facilities,
            'rows' => $facility ? $this->leadership->formRowsForFacility($facility) : collect(),
            'roleDefinitions' => $facility ? $this->leadership->roleDefinitionsForFacility($facility) : [],
            'employeeOptions' => $facility ? $this->leadership->employeeNameOptionsForFacility($facility) : collect(),
            'canEdit' => $canEdit,
            'canRemoveRoles' => $canEdit && $user->hasRole(['admin', 'super-admin', 'facility-dsd']),
        ]);
    }
}
