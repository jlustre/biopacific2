<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Models\Facility;
use App\Models\PortalHelpRequest;
use App\Services\PortalHelpRequestService;
use App\Support\SelectedFacility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MemberPortalHelpController extends Controller
{
    use ProvidesMemberPortalContext;

    public function index(Request $request): View
    {
        $user = $request->user();
        $requests = PortalHelpRequest::query()
            ->with('facility:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.member.help.index', array_merge($this->memberPortalContext($user), [
            'helpRequests' => $requests,
            'portalActive' => 'help',
            'portalTitle' => 'Help Center | Bio Pacific',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => 'My help requests',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function hrForm(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.member.help.hr', array_merge($this->memberPortalContext($user), $this->formContext($user, PortalHelpRequest::TYPE_HR)));
    }

    public function supportForm(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.member.help.support', array_merge($this->memberPortalContext($user), $this->formContext($user, PortalHelpRequest::TYPE_SUPPORT)));
    }

    public function storeHr(Request $request, PortalHelpRequestService $service): RedirectResponse
    {
        return $this->storeRequest($request, $service, PortalHelpRequest::TYPE_HR);
    }

    public function storeSupport(Request $request, PortalHelpRequestService $service): RedirectResponse
    {
        return $this->storeRequest($request, $service, PortalHelpRequest::TYPE_SUPPORT);
    }

    public function show(Request $request, PortalHelpRequest $helpRequest): View
    {
        $user = $request->user();
        $this->authorizeHelpRequest($user, $helpRequest);
        $helpRequest->load('facility:id,name');

        return view('dashboard.member.help.show', array_merge($this->memberPortalContext($user), [
            'helpRequest' => $helpRequest,
            'portalActive' => 'help',
            'portalTitle' => $helpRequest->subject . ' | Help Center',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => $helpRequest->typeLabel(),
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function confirmation(Request $request, PortalHelpRequest $helpRequest): View
    {
        $user = $request->user();
        $this->authorizeHelpRequest($user, $helpRequest);
        $helpRequest->load('facility:id,name');

        return view('dashboard.member.help.confirmation', array_merge($this->memberPortalContext($user), [
            'helpRequest' => $helpRequest,
            'portalActive' => 'help',
            'portalTitle' => 'Request submitted | Help Center',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => 'Request submitted',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    protected function storeRequest(Request $request, PortalHelpRequestService $service, string $type): RedirectResponse
    {
        $user = $request->user();
        $canPickFacility = $this->facilitiesForUser($user)->count() > 1;
        $rules = $type === PortalHelpRequest::TYPE_HR
            ? $service->hrValidationRules($canPickFacility)
            : $service->supportValidationRules($canPickFacility);

        $validated = $request->validate($rules);

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        $facilityId = $canPickFacility
            ? (int) $validated['facility_id']
            : ($this->defaultFacilityIdForUser($user) ?? (int) ($validated['facility_id'] ?? 0) ?: null);

        $helpRequest = $service->createRequest([
            'user_id' => $user->id,
            'facility_id' => $facilityId,
            'type' => $type,
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'normal',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'employee_num' => $employee?->employee_num,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'preferred_contact' => $validated['preferred_contact'],
            'best_time_to_reach' => $validated['best_time_to_reach'] ?? null,
            'steps_to_reproduce' => $validated['steps_to_reproduce'] ?? null,
            'no_phi_confirmed' => true,
        ], $type === PortalHelpRequest::TYPE_SUPPORT ? $service->attachmentFilesFromRequest($request) : []);

        return redirect()->route('member.help.confirmation', $helpRequest);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formContext($user, string $type): array
    {
        $isHr = $type === PortalHelpRequest::TYPE_HR;
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        return [
            'formType' => $type,
            'categories' => config($isHr ? 'portal-help.hr_categories' : 'portal-help.support_categories', []),
            'preferredContactOptions' => config('portal-help.preferred_contact_options', []),
            'bestTimeOptions' => config('portal-help.best_time_options', []),
            'facilities' => $this->facilitiesForUser($user),
            'defaultFacilityId' => $this->defaultFacilityIdForUser($user),
            'prefillName' => $user->name,
            'prefillEmail' => $user->email,
            'prefillPhone' => $employee?->phone?->phone_number,
            'prefillEmployeeNum' => $employee?->employee_num,
            'portalActive' => $isHr ? 'help-hr' : 'help-support',
            'portalTitle' => ($isHr ? 'Email HR' : 'Support Request') . ' | Bio Pacific',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => $isHr ? 'Email HR' : 'Submit support request',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ];
    }

    protected function authorizeHelpRequest($user, PortalHelpRequest $helpRequest): void
    {
        if ((int) $helpRequest->user_id !== (int) $user->id) {
            throw new NotFoundHttpException();
        }
    }

    protected function facilitiesForUser($user)
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return Facility::query()->orderBy('name')->get(['id', 'name']);
        }

        $facilityId = $this->defaultFacilityIdForUser($user);

        return $facilityId
            ? Facility::query()->whereKey($facilityId)->get(['id', 'name'])
            : collect();
    }

    protected function defaultFacilityIdForUser($user): ?int
    {
        if ($facilityId = SelectedFacility::id()) {
            return $facilityId;
        }

        if ($user->facility_id) {
            return (int) $user->facility_id;
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        $assignmentFacilityId = $employee?->currentAssignment?->facility_id;

        return $assignmentFacilityId ? (int) $assignmentFacilityId : null;
    }
}
