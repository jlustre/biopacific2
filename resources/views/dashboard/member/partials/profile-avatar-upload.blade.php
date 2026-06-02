@php
    $avatarUrl = $avatarUrl ?? null;
@endphp

<div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5" x-data="{
  previewUrl: @js($avatarUrl),
  onFileChange(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
      URL.revokeObjectURL(this.previewUrl);
    }
    this.previewUrl = URL.createObjectURL(file);
  }
}">
    <h3 class="text-sm font-black text-slate-900">Profile photo</h3>
    <p class="mt-0.5 text-xs text-slate-500">Upload a photo for your avatar across the member portal.</p>

    @if(session('status') === 'avatar-updated')
    <p class="mt-3 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">Photo updated successfully.</p>
    @elseif(session('status') === 'avatar-removed')
    <p class="mt-3 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700">Photo removed.</p>
    @endif

    @error('avatar')
    <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">{{ $message }}</p>
    @enderror

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="relative shrink-0">
            <template x-if="previewUrl">
                <img :src="previewUrl" alt="" class="h-24 w-24 rounded-2xl object-cover ring-2 ring-white shadow-md">
            </template>
            <template x-if="!previewUrl">
                @include('dashboard.member.partials.user-avatar', [
                    'avatarUrl' => null,
                    'initials' => $initials,
                    'size' => 'lg',
                    'shape' => 'rounded-2xl',
                    'imgClass' => 'ring-2 ring-white shadow-md',
                ])
            </template>
        </div>

        <div class="min-w-0 flex-1 space-y-3">
            <form method="POST" action="{{ route('settings.profile.avatar.update') }}" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <label class="block">
                    <span class="sr-only">Choose profile photo</span>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required
                           @change="onFileChange($event)"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-teal-800 hover:file:bg-teal-100"/>
                </label>
                <p class="text-xs text-slate-500">JPG, PNG, or WebP. Max 2 MB. Square images work best.</p>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">
                    <i class="fa-solid fa-upload text-xs"></i> Upload photo
                </button>
            </form>

            @if($avatarUrl)
            <form method="POST" action="{{ route('settings.profile.avatar.destroy') }}"
                  onsubmit="return confirm('Remove your profile photo?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-800">Remove photo</button>
            </form>
            @endif
        </div>
    </div>
</div>
