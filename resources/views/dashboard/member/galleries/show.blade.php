@extends('layouts.member-portal')

@php
    $lightboxPhotos = $gallery->images->values()->map(fn ($image) => [
        'id' => $image->id,
        'url' => $image->publicUrl(),
        'caption' => $image->displayCaption() ?: $image->title,
    ])->all();
@endphp

@section('content')
<section
    class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5"
    x-data="galleryLightbox(@js($lightboxPhotos))"
    @keydown.escape.window="if (open) close()"
    @keydown.arrow-left.window="if (open) prev()"
    @keydown.arrow-right.window="if (open) next()"
>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('member.galleries.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">← All galleries</a>
        @if($canManage)
        <div class="flex flex-wrap gap-2">
            <a href="#upload-photos"
               class="rounded-xl bg-teal-600 px-3 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700">
                Upload photos
            </a>
            <a href="#manage-photos"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm hover:border-teal-300 hover:bg-teal-50">
                Manage photos
            </a>
            <a href="{{ route('member.galleries.edit', $gallery) }}"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm hover:border-teal-300 hover:bg-teal-50">
                Edit gallery
            </a>
            <form method="POST" action="{{ route('member.galleries.destroy', $gallery) }}"
                  onsubmit="return confirm('Delete this gallery and all of its photos? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 shadow-sm hover:bg-rose-50">
                    Delete gallery
                </button>
            </form>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-br from-teal-50 via-white to-slate-50 px-4 py-5 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($gallery->isPublished())
                            <span class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-black uppercase tracking-wide text-white shadow-sm">
                                Published
                            </span>
                        @else
                            <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-black uppercase tracking-wide text-white shadow-sm">
                                Unpublished
                            </span>
                        @endif
                        @if($canManage)
                            <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-teal-700">You own this</span>
                        @endif
                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-teal-800">
                            {{ $gallery->visibility_label }}
                        </span>
                        @if($gallery->isSharedBeyondOwner())
                            <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-sky-800">Shared with facilities</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-700">This facility only</span>
                        @endif
                        @if(!empty($isSharedReadOnly))
                            <span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-violet-800">Read-only</span>
                        @endif
                    </div>
                    <h1 class="mt-2 text-xl font-black tracking-tight text-slate-900 sm:text-2xl">{{ $gallery->title }}</h1>
                    @if($gallery->year)
                        <p class="mt-1 text-sm font-semibold text-slate-700">Year {{ $gallery->year }}</p>
                    @endif
                    @if($gallery->event)
                        <p class="mt-1 text-sm font-semibold text-teal-700">
                            <i class="fa-solid fa-calendar-day mr-1"></i>
                            Linked event: {{ $gallery->event->title }}
                            @if($gallery->event->event_date)
                                ({{ $gallery->event->event_date->format('M j, Y') }})
                            @endif
                        </p>
                    @endif
                    @if($gallery->isSharedBeyondOwner() && $gallery->sharedFacilities->isNotEmpty())
                        <p class="mt-1 text-xs text-slate-600">
                            Shared with:
                            {{ $gallery->sharedFacilities->pluck('name')->join(', ') }}
                        </p>
                    @endif
                    @if(!empty($isSharedReadOnly) && $gallery->facility)
                        <p class="mt-1 text-xs font-semibold text-violet-700">Shared from {{ $gallery->facility->name }}</p>
                    @endif
                    @if($gallery->description)
                        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">{{ $gallery->description }}</p>
                    @endif
                    <p class="mt-2 text-xs text-slate-500">
                        {{ $gallery->images->count() }} {{ \Illuminate\Support\Str::plural('photo', $gallery->images->count()) }}
                        @if($gallery->creator)
                            · Created by {{ $gallery->creator->name }}
                        @endif
                        @if(! $canManage && empty($isSharedReadOnly))
                            · Only the creator can edit or delete this gallery
                        @elseif(!empty($isSharedReadOnly))
                            · Shared with your facility for viewing only
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if($canManage)
        <div id="upload-photos" class="scroll-mt-24 border-b border-slate-100 px-4 py-5 sm:px-6" x-data="{ files: [], previews: [], onFiles(e) {
            this.files = Array.from(e.target.files || []);
            this.previews = [];
            this.files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (ev) => { this.previews[index] = ev.target.result; this.previews = [...this.previews]; };
                reader.readAsDataURL(file);
            });
        }}">
            <h2 class="text-sm font-bold text-slate-900">Upload more photos</h2>
            <p class="mt-1 text-xs text-slate-500">Upload one or more images and add a caption for each.</p>
            <form method="POST" action="{{ route('member.galleries.images.store', $gallery) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Images</label>
                    <input type="file" name="images[]" accept="image/*" multiple required
                           @change="onFiles($event)"
                           class="mt-1 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-teal-800 hover:file:bg-teal-100">
                </div>

                <template x-if="files.length">
                    <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                    <template x-if="previews[index]">
                                        <img :src="previews[index]" class="h-full w-full object-cover" alt="">
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-semibold text-slate-700" x-text="file.name"></p>
                                    <label class="mt-2 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Caption</label>
                                    <input type="text" :name="'captions['+index+']'" maxlength="500"
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm"
                                           placeholder="Optional caption for this photo">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Upload photos
                </button>
            </form>
        </div>
        @endif

        <div id="manage-photos" class="scroll-mt-24 p-4 sm:p-6">
            @if($gallery->images->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-slate-800">No photos in this gallery yet</p>
                    @if($canManage)
                        <p class="mt-1 text-sm text-slate-500">Upload images above, then manage or delete them here.</p>
                        <a href="#upload-photos" class="mt-3 inline-flex text-sm font-bold text-teal-700 hover:underline">Go to upload →</a>
                    @endif
                </div>
            @else
                @if($canManage)
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-sm font-bold text-slate-900">Manage photos</h2>
                        <p class="text-xs text-slate-500">Edit captions or remove photos you no longer need.</p>
                    </div>
                @endif
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($gallery->images as $image)
                        @php
                            $url = $image->publicUrl();
                            $caption = $image->displayCaption();
                        @endphp
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <button type="button" class="block w-full text-left" @click="openById({{ $image->id }})">
                                <div class="aspect-square overflow-hidden bg-slate-100">
                                    @if($url)
                                        <img src="{{ $url }}" alt="{{ $caption ?: $image->title }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                            </button>
                            <div class="space-y-2 p-3">
                                @if($canManage)
                                    <div class="space-y-2">
                                        <form id="update-image-{{ $image->id }}" method="POST" action="{{ route('member.galleries.images.update', [$gallery, $image]) }}">
                                            @csrf
                                            @method('PUT')
                                            <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-500">Caption</label>
                                            <textarea name="caption" rows="2" maxlength="500"
                                                      class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-200"
                                                      placeholder="Add a caption…">{{ $caption }}</textarea>
                                        </form>
                                        <div class="flex items-center justify-between gap-2">
                                            <button type="submit"
                                                    form="update-image-{{ $image->id }}"
                                                    class="text-xs font-bold text-teal-700 hover:underline">
                                                Save Caption
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('member.galleries.images.destroy', [$gallery, $image]) }}"
                                                  onsubmit="return confirm('Remove this photo? The file will also be deleted from storage.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">
                                                    Remove Photo
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <p class="line-clamp-3 text-xs text-slate-600">{{ $caption ?: '—' }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Lightbox carousel --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Photo viewer">
        <div class="absolute inset-0 bg-slate-900/70" @click="close()"></div>

        <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-6">
            <div class="relative flex w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                 @click.stop>
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-500">
                        <span x-text="index + 1"></span>
                        <span> of </span>
                        <span x-text="photos.length"></span>
                    </p>
                    <button type="button"
                            @click="close()"
                            class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
                            aria-label="Close">
                        Close
                    </button>
                </div>

                <div class="relative bg-slate-950">
                    <template x-if="current?.url">
                        <img :src="current.url"
                             :alt="current.caption || ''"
                             class="mx-auto max-h-[70vh] w-full object-contain transition-opacity duration-200"
                             x-transition.opacity>
                    </template>

                    <button type="button"
                            x-show="photos.length > 1"
                            @click="prev()"
                            class="absolute left-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-800 shadow-lg transition hover:bg-white"
                            aria-label="Previous photo">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button"
                            x-show="photos.length > 1"
                            @click="next()"
                            class="absolute right-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-800 shadow-lg transition hover:bg-white"
                            aria-label="Next photo">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="space-y-1 px-5 py-4">
                    <p class="text-sm font-medium text-slate-800" x-text="current?.caption || '—'"></p>
                    <p class="text-xs text-slate-500" x-show="photos.length > 1">Use the arrows or keyboard ← → to browse photos.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('head')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('galleryLightbox', (photos = []) => ({
        photos,
        open: false,
        index: 0,
        get current() {
            return this.photos[this.index] ?? null;
        },
        openById(id) {
            const found = this.photos.findIndex((photo) => photo.id === id);
            this.index = found >= 0 ? found : 0;
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        next() {
            if (!this.photos.length) return;
            this.index = (this.index + 1) % this.photos.length;
        },
        prev() {
            if (!this.photos.length) return;
            this.index = (this.index - 1 + this.photos.length) % this.photos.length;
        },
    }));
});
</script>
@endpush
