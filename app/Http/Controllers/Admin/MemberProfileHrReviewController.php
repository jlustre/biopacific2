<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BPEmployee;
use App\Services\MemberProfileHrReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberProfileHrReviewController extends Controller
{
    public function confirm(Request $request, BPEmployee $employee, MemberProfileHrReviewService $reviewService): RedirectResponse
    {
        $this->authorizeHrReviewer($request);

        $user = $employee->user;
        if (! $user && filled($employee->email)) {
            $user = \App\Models\User::query()->where('email', $employee->email)->first();
        }

        if (! $user) {
            return back()->withErrors(['profile_hr' => 'No portal user is linked to this employee.']);
        }

        $reviewService->confirmProfile($user, $request->user());

        return back()->with('success', 'Employee profile confirmed by HR.');
    }

    protected function authorizeHrReviewer(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && method_exists($user, 'hasRole') && $user->hasRole([
                'admin',
                'super-admin',
                'rdhr',
                'facility-admin',
                'facility-dsd',
                'don',
            ]),
            403
        );
    }
}
