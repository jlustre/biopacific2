<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberProfileAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberProfileAvatarController extends Controller
{
    public function show(Request $request): BinaryFileResponse
    {
        $path = str_replace('\\', '/', ltrim((string) $request->user()->avatar_path, '/'));

        abort_if($path === '', 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

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
