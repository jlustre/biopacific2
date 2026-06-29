<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Models\Facility;
use App\Models\WebmasterContact;
use App\Services\WebmasterContactService;
use App\Support\SelectedFacility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MemberPortalFeedbackController extends Controller
{
    use ProvidesMemberPortalContext;

    public function index(Request $request): View
    {
        $user = $request->user();
        $submissions = WebmasterContact::query()
            ->with('facility:id,name,slug')
            ->withCount('comments')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.member.feedback.index', array_merge($this->memberPortalContext($user), [
            'submissions' => $submissions,
            'portalActive' => 'feedback',
            'portalTitle' => 'Report Issue or Idea | Bio Pacific',
            'portalEyebrow' => 'Help & Feedback',
            'portalPageTitle' => 'Report Issue or Idea',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function show(Request $request, WebmasterContact $submission): View
    {
        $user = $request->user();
        $submission = $this->authorizeMemberSubmission($user, $submission);

        $submission->load([
            'facility:id,name,slug',
            'comments.user:id,name',
        ]);

        return view('dashboard.member.feedback.show', array_merge($this->memberPortalContext($user), [
            'submission' => $submission,
            'canEdit' => $submission->isOpenForMemberUpdates(),
            'portalActive' => 'feedback',
            'portalTitle' => $submission->subject . ' | Feedback',
            'portalEyebrow' => 'Help & Feedback',
            'portalPageTitle' => 'Submission details',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.member.feedback.create', array_merge($this->memberPortalContext($user), [
            'categoryOptions' => app(WebmasterContactService::class)->categoryOptions(),
            'facilities' => $this->facilitiesForUser($user),
            'defaultFacilityId' => $this->defaultFacilityIdForUser($user),
            'prefillName' => $user->name,
            'prefillEmail' => $user->email,
            'portalActive' => 'feedback',
            'portalTitle' => 'Submit Feedback | Bio Pacific',
            'portalEyebrow' => 'Help & Feedback',
            'portalPageTitle' => 'Submit feedback',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function store(Request $request, WebmasterContactService $service): RedirectResponse
    {
        $user = $request->user();
        $canPickFacility = $this->facilitiesForUser($user)->count() > 1;

        $validated = $request->validate(array_merge($service->validationRules($canPickFacility), [
            'category' => 'required|string|in:issue,enhancement',
        ]));

        $facilityId = $canPickFacility
            ? (int) $validated['facility_id']
            : ($this->defaultFacilityIdForUser($user) ?? (int) ($validated['facility_id'] ?? 0) ?: null);

        $contact = $service->createSubmission([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'urgent' => $request->boolean('urgent'),
            'facility_id' => $facilityId,
            'category' => $validated['category'],
            'source' => WebmasterContactService::SOURCE_MEMBER_PORTAL,
            'user_id' => $user->id,
        ], $service->screenshotFilesFromRequest($request));

        return redirect()
            ->route('member.feedback.show', $contact)
            ->with('success', 'Your feedback was submitted. You can add comments or update it while it remains open.');
    }

    public function edit(Request $request, WebmasterContact $submission): View|RedirectResponse
    {
        $user = $request->user();
        $submission = $this->authorizeMemberSubmission($user, $submission);

        if (! $submission->isOpenForMemberUpdates()) {
            return redirect()
                ->route('member.feedback.show', $submission)
                ->with('error', 'Resolved submissions cannot be edited. You can still view the conversation.');
        }

        return view('dashboard.member.feedback.edit', array_merge($this->memberPortalContext($user), [
            'submission' => $submission,
            'categoryOptions' => app(WebmasterContactService::class)->categoryOptions(),
            'facilities' => $this->facilitiesForUser($user),
            'portalActive' => 'feedback',
            'portalTitle' => 'Edit Feedback | Bio Pacific',
            'portalEyebrow' => 'Help & Feedback',
            'portalPageTitle' => 'Edit submission',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function update(Request $request, WebmasterContact $submission, WebmasterContactService $service): RedirectResponse
    {
        $user = $request->user();
        $submission = $this->authorizeMemberSubmission($user, $submission);

        if (! $submission->isOpenForMemberUpdates()) {
            return redirect()
                ->route('member.feedback.show', $submission)
                ->with('error', 'Resolved submissions cannot be edited.');
        }

        $canPickFacility = $this->facilitiesForUser($user)->count() > 1;

        $validated = $request->validate(array_merge($service->validationRules($canPickFacility), [
            'category' => 'required|string|in:issue,enhancement',
        ]));

        $facilityId = $canPickFacility
            ? (int) $validated['facility_id']
            : ($submission->facility_id ?? $this->defaultFacilityIdForUser($user));

        $service->updateMemberSubmission($submission, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'urgent' => $request->boolean('urgent'),
            'facility_id' => $facilityId,
            'category' => $validated['category'],
        ], $service->screenshotFilesFromRequest($request));

        $service->notifyWebmasterOfActivity($submission->fresh(), 'Member updated submission');

        return redirect()
            ->route('member.feedback.show', $submission)
            ->with('success', 'Your submission was updated.');
    }

    public function storeComment(Request $request, WebmasterContact $submission, WebmasterContactService $service): RedirectResponse
    {
        $user = $request->user();
        $submission = $this->authorizeMemberSubmission($user, $submission);

        if (! $submission->isOpenForMemberUpdates()) {
            return redirect()
                ->route('member.feedback.show', $submission)
                ->with('error', 'This submission is resolved and no longer accepts new comments.');
        }

        $validated = $request->validate($service->commentValidationRules());

        $service->addComment($submission, $validated['body'], $user);
        $service->notifyWebmasterOfActivity($submission->fresh(), 'Member added comment');

        return redirect()
            ->route('member.feedback.show', $submission)
            ->with('success', 'Your comment was added.');
    }

    protected function authorizeMemberSubmission($user, WebmasterContact $submission): WebmasterContact
    {
        if (! $submission->ownedByUser($user)) {
            throw new NotFoundHttpException();
        }

        return $submission;
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
