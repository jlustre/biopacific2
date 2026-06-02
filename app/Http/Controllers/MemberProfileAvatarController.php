<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberProfileAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberProfileAvatarController extends Controller
{
    public function update(MemberProfileAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->storeAvatar($request->file('avatar'));
        auth()->setUser($user->fresh());

        return redirect()
            ->route('settings.profile')
            ->with('status', 'avatar-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->removeProfileAvatar();
        auth()->setUser($user->fresh());

        return redirect()
            ->route('settings.profile')
            ->with('status', 'avatar-removed');
    }
}
