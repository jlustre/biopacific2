<?php

namespace App\Http\Controllers;

use App\Services\MemberProfileHrReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberProfileHrReviewController extends Controller
{
    public function submit(Request $request, MemberProfileHrReviewService $reviewService): RedirectResponse
    {
        $user = $request->user();

        if (! $reviewService->submitForHrReview($user)) {
            return redirect()
                ->route('settings.profile')
                ->withErrors(['profile_hr' => 'Complete your account details and primary emergency contact before submitting for HR review.']);
        }

        return redirect()
            ->route('settings.profile')
            ->with('status', 'profile-submitted-hr');
    }
}
