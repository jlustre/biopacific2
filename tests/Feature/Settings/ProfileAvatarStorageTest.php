<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacing_profile_photo_deletes_previous_file_from_storage(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('settings.profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('old-avatar.jpg'),
            ])
            ->assertRedirect(route('settings.profile'));

        $oldPath = $user->refresh()->avatar_path;

        $this->assertNotNull($oldPath);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($user)
            ->post(route('settings.profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('new-avatar.jpg'),
            ])
            ->assertRedirect(route('settings.profile'));

        $newPath = $user->refresh()->avatar_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_removing_profile_photo_deletes_avatar_files_from_storage(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('settings.profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertRedirect(route('settings.profile'));

        $avatarPath = $user->refresh()->avatar_path;

        $this->assertNotNull($avatarPath);
        Storage::disk('public')->assertExists($avatarPath);

        $this->actingAs($user)
            ->delete(route('settings.profile.avatar.destroy'))
            ->assertRedirect(route('settings.profile'));

        $this->assertNull($user->refresh()->avatar_path);
        Storage::disk('public')->assertMissing($avatarPath);
        Storage::disk('public')->assertMissing('profile-avatars/' . $user->id);
    }
}
