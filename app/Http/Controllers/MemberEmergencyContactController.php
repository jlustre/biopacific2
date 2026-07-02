<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberEmergencyContactRequest;
use App\Models\MemberEmergencyContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberEmergencyContactController extends Controller
{
    public function store(MemberEmergencyContactRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $hasPrimary = MemberEmergencyContact::query()
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->exists();

        $data['user_id'] = $user->id;
        $data['is_primary'] = $hasPrimary ? $request->boolean('is_primary') : true;
        $data['sort_order'] = (int) MemberEmergencyContact::query()->where('user_id', $user->id)->max('sort_order') + 1;

        if ($data['is_primary']) {
            $this->clearPrimaryForUser($user->id);
        }

        MemberEmergencyContact::create($data);

        $this->submitProfileForHrIfReady($user);

        return $this->redirectToProfile();
    }

    public function update(MemberEmergencyContactRequest $request, MemberEmergencyContact $emergencyContact): RedirectResponse
    {
        $this->authorizeContact($request, $emergencyContact);

        $data = $request->validated();

        $data['is_primary'] = $request->boolean('is_primary');

        if ($data['is_primary']) {
            $this->clearPrimaryForUser($emergencyContact->user_id, $emergencyContact->id);
        }

        $emergencyContact->update($data);
        $this->ensurePrimaryContactExists($emergencyContact->user_id);

        $this->submitProfileForHrIfReady($request->user());

        return $this->redirectToProfile('emergency-contact-saved');
    }

    public function destroy(Request $request, MemberEmergencyContact $emergencyContact): RedirectResponse
    {
        $this->authorizeContact($request, $emergencyContact);

        $userId = $emergencyContact->user_id;
        $wasPrimary = $emergencyContact->is_primary;
        $emergencyContact->delete();

        if ($wasPrimary) {
            $next = MemberEmergencyContact::query()
                ->where('user_id', $userId)
                ->orderBy('sort_order')
                ->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return $this->redirectToProfile('emergency-contact-deleted');
    }

    public function setPrimary(Request $request, MemberEmergencyContact $emergencyContact): RedirectResponse
    {
        $this->authorizeContact($request, $emergencyContact);

        $this->clearPrimaryForUser($emergencyContact->user_id);
        $emergencyContact->update(['is_primary' => true]);

        $this->submitProfileForHrIfReady($request->user());

        return $this->redirectToProfile('emergency-contact-primary');
    }

    protected function redirectToProfile(string $status = 'emergency-contact-saved'): RedirectResponse
    {
        return redirect()
            ->route('settings.profile')
            ->withFragment('emergency-contacts')
            ->with('status', $status);
    }

    protected function authorizeContact(Request $request, MemberEmergencyContact $contact): void
    {
        if ((int) $contact->user_id !== (int) $request->user()->id) {
            abort(403);
        }
    }

    protected function clearPrimaryForUser(int $userId, ?int $exceptId = null): void
    {
        $query = MemberEmergencyContact::query()->where('user_id', $userId);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_primary' => false]);
    }

    protected function ensurePrimaryContactExists(int $userId): void
    {
        $hasPrimary = MemberEmergencyContact::query()
            ->where('user_id', $userId)
            ->where('is_primary', true)
            ->exists();

        if ($hasPrimary) {
            return;
        }

        $next = MemberEmergencyContact::query()
            ->where('user_id', $userId)
            ->orderBy('sort_order')
            ->first();

        $next?->update(['is_primary' => true]);
    }

    protected function submitProfileForHrIfReady($user): void
    {
        app(\App\Services\MemberProfileHrReviewService::class)->submitForHrReview($user->fresh());
    }
}
