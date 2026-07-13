<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\FacilityDashboardService;
use App\Services\RoleMemberDashboardService;
use App\Support\MemberPortalLayout;
use App\Support\Rbac\Permissions;
use App\Support\SelectedFacility;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacilityChecklistPartController extends Controller
{
    use ProvidesMemberPortalContext;

    /**
     * Facility → employee picker for a checklist part (Part G Competencies / Part F Performance).
     * Reuses the existing employees roster and employee edit checklist — no new Part UI.
     */
    public function competencies(Request $request)
    {
        return $this->checklistEntry($request, 'partG', [
            'switch_route' => 'admin.facility.competencies',
            'portal_active' => 'facility-competencies',
            'portal_title' => 'Competencies | Bio Pacific',
            'lead' => 'Choose a facility, then select an employee to open Checklist Part G (Competencies).',
            'forbidden' => 'Unauthorized access to facility competencies.',
        ]);
    }

    public function performance(Request $request)
    {
        return $this->checklistEntry($request, 'partF', [
            'switch_route' => 'admin.facility.performance',
            'portal_active' => 'facility-performance',
            'portal_title' => 'Performance | Bio Pacific',
            'lead' => 'Choose a facility, then select an employee to open Checklist Part F (Performance).',
            'forbidden' => 'Unauthorized access to facility performance.',
        ]);
    }

    /**
     * Facility → employee picker for Part H Trainings (progress monitoring).
     */
    public function trainings(Request $request)
    {
        $user = $request->user();

        if (! $user || (! MemberPortalLayout::userCanAccessTrainingManagementNav($user) && ! $user->can(Permissions::ACCESS_HR_PORTAL))) {
            abort(403, 'Unauthorized access to facility trainings.');
        }

        return $this->resolveFacilityOrSelect($request, [
            'switch_route' => 'admin.facility.trainings',
            'portal_active' => 'facility-trainings',
            'portal_title' => 'Trainings | Bio Pacific',
            'lead' => 'Choose a facility, then search or select an employee to monitor training progress (Checklist Part H).',
            'destination' => fn (Facility $facility) => redirect()->route('admin.facility.employees', [
                'facility' => $facility->getRouteKey(),
                'checklist' => 'partH',
            ]),
        ]);
    }

    /**
     * Facility → Documents page (employee upload/select on the existing facility documents screen).
     */
    public function documents(Request $request)
    {
        $user = $request->user();

        if (! $user || ! MemberPortalLayout::userCanAccessDocumentsManagement($user)) {
            abort(403, 'Unauthorized access to facility documents.');
        }

        return $this->resolveFacilityOrSelect($request, [
            'switch_route' => 'admin.facility.documents.entry',
            'portal_active' => 'facility-documents',
            'portal_title' => 'Documents | Bio Pacific',
            'lead' => 'Choose a facility, then select an employee on the Documents page.',
            'destination' => fn (Facility $facility) => redirect()->route('admin.facility.documents', [
                'facility' => $facility->getRouteKey(),
            ]),
        ]);
    }

    /**
     * @param  array{switch_route: string, portal_active: string, portal_title: string, lead: string, forbidden: string}  $meta
     */
    protected function checklistEntry(Request $request, string $checklistPart, array $meta): Response
    {
        $user = $request->user();

        if (! $user || ! $user->can(Permissions::ACCESS_HR_PORTAL)) {
            abort(403, $meta['forbidden']);
        }

        return $this->resolveFacilityOrSelect($request, [
            'switch_route' => $meta['switch_route'],
            'portal_active' => $meta['portal_active'],
            'portal_title' => $meta['portal_title'],
            'lead' => $meta['lead'],
            'destination' => fn (Facility $facility) => redirect()->route('admin.facility.employees', [
                'facility' => $facility->getRouteKey(),
                'checklist' => $checklistPart,
            ]),
        ]);
    }

    /**
     * @param  array{switch_route: string, portal_active: string, portal_title: string, lead: string, destination: callable(Facility): Response}  $meta
     */
    protected function resolveFacilityOrSelect(Request $request, array $meta): Response
    {
        $user = $request->user();
        $facilityService = app(FacilityDashboardService::class);
        $roleDashboard = app(RoleMemberDashboardService::class);
        $facilities = $facilityService->facilitiesForUser($user);

        $facility = SelectedFacility::model($request);

        if (! $facility && $facilities->count() === 1) {
            $facility = $facilities->first();
        }

        if (! $facility && $user->facility_id) {
            $facility = Facility::query()->find($user->facility_id);
        }

        if ($facility && ! SelectedFacility::userCanAccessFacility($user, $facility)) {
            abort(403, 'You do not have access to this facility.');
        }

        if ($facility) {
            SelectedFacility::remember($facility);

            return ($meta['destination'])($facility);
        }

        return response()->view('dashboard.member.facility-select', array_merge($this->memberPortalContext($user), [
            'facilities' => $facilities,
            'facilitySwitchRoute' => $meta['switch_route'],
            'facilitySelectLead' => $meta['lead'],
            'organizationStats' => $roleDashboard->organizationOverviewStatsForUser($user),
            'portalActive' => $meta['portal_active'],
            'portalTitle' => $meta['portal_title'],
            'portalEyebrow' => 'Facility',
            'portalPageTitle' => 'Select a facility',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }
}
