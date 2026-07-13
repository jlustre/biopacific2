@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-br from-teal-50 via-white to-slate-50 px-4 py-5 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-teal-100/90 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-teal-800">
                        <i class="fa-solid fa-images"></i>
                        Facility Photo Galleries
                    </div>
                    <h1 class="mt-3 text-xl font-black tracking-tight text-slate-900 sm:text-2xl">Photo Galleries</h1>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                        Event albums for
                        <span class="font-semibold text-slate-800">{{ $facility->name }}</span>.
                        @if($canManage)
                            Create a gallery, publish it when ready, and manage photos if you own the album.
                        @else
                            Browse facility galleries and photo albums. Viewing is available to all facility employees.
                        @endif
                    </p>
                </div>
                @if($canManage)
                <a href="{{ route('member.galleries.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-teal-700">
                    <i class="fa-solid fa-plus"></i>
                    New gallery
                </a>
                @endif
            </div>
        </div>

        <div class="p-4 sm:p-6">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">{{ session('error') }}</div>
            @endif

            @php
                $filters = $filters ?? ['q' => '', 'year' => null];
                $availableYears = $availableYears ?? [];
                $hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['year'] ?? null);
            @endphp

            <form method="GET" action="{{ route('member.galleries.index') }}" class="mb-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <label for="gallery-search" class="block text-[11px] font-bold uppercase tracking-wide text-slate-500">Search</label>
                    <input type="search" name="q" id="gallery-search" value="{{ $filters['q'] ?? '' }}"
                           placeholder="Search by title, description, or event…"
                           class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                </div>
                <div class="w-full sm:w-40">
                    <label for="gallery-year" class="block text-[11px] font-bold uppercase tracking-wide text-slate-500">Year</label>
                    <select name="year" id="gallery-year"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        <option value="">All years</option>
                        @foreach($availableYears as $yearOption)
                            <option value="{{ $yearOption }}" @selected((string) ($filters['year'] ?? '') === (string) $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">
                        Filter
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('member.galleries.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            @if($albums->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-slate-200">🖼️</div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">
                        {{ $hasActiveFilters ? 'No matching galleries' : 'No galleries yet' }}
                    </h2>
                    <p class="mx-auto mt-1.5 max-w-md text-sm text-slate-500">
                        @if($hasActiveFilters)
                            Try another year or clear your search.
                        @else
                            Start with an event album so photos stay organized and easy to find.
                        @endif
                    </p>
                    @if($hasActiveFilters)
                        <a href="{{ route('member.galleries.index') }}" class="mt-6 inline-flex text-sm font-semibold text-teal-700 hover:underline">Clear filters</a>
                    @elseif($canManage)
                    <a href="{{ route('member.galleries.create') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700">
                        Create first gallery
                    </a>
                    @endif
                </div>
            @else
                <div class="mb-3 flex items-center justify-between gap-2 text-xs text-slate-500">
                    <span>{{ $albums->count() }} {{ \Illuminate\Support\Str::plural('gallery', $albums->count()) }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($albums as $album)
                        @php
                            $cover = $album->images->first();
                            $isOwner = auth()->user()?->can('update', $album) ?? false;
                            $isSharedIn = ! $album->isOwnedByFacility((int) $facility->id);
                            $published = $album->isPublished();
                        @endphp
                        <article @class([
                            'overflow-hidden rounded-2xl border bg-white shadow-sm',
                            'border-emerald-200' => $published,
                            'border-amber-300' => ! $published,
                        ])>
                            <a href="{{ route('member.galleries.show', $album) }}" class="block group">
                                <div @class([
                                    'relative aspect-[16/10] overflow-hidden',
                                    'bg-slate-100' => $published,
                                    'bg-amber-50' => ! $published,
                                ])>
                                    @if($cover?->image_url)
                                        <img src="{{ asset('storage/'.$cover->image_url) }}" alt=""
                                             @class([
                                                 'h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]',
                                                 'opacity-70' => ! $published,
                                             ])>
                                    @else
                                        <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 via-teal-50 to-slate-200 text-4xl">🖼️</div>
                                    @endif

                                    <div class="absolute inset-x-0 top-0 flex flex-wrap items-start justify-between gap-2 p-3">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if($album->year)
                                                <span class="rounded-full bg-slate-900/75 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                                                    {{ $album->year }}
                                                </span>
                                            @endif
                                            <span class="rounded-full bg-white/95 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-700 shadow-sm">
                                                {{ $album->images->count() }} {{ \Illuminate\Support\Str::plural('photo', $album->images->count()) }}
                                            </span>
                                        </div>
                                        <span @class([
                                            'rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-wide shadow-sm',
                                            'bg-emerald-500 text-white' => $published,
                                            'bg-amber-500 text-white' => ! $published,
                                        ])>
                                            {{ $published ? 'Published' : 'Unpublished' }}
                                        </span>
                                    </div>
                                </div>
                            </a>

                            <div class="space-y-3 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('member.galleries.show', $album) }}" class="block text-base font-bold leading-snug text-slate-900 hover:text-teal-800">
                                            {{ $album->title }}
                                        </a>
                                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                            @if($isOwner)
                                                <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-bold uppercase text-teal-700">You own this</span>
                                            @elseif($isSharedIn)
                                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">Shared · Read-only</span>
                                            @endif
                                            @if($album->isSharedBeyondOwner() && ! $isSharedIn)
                                                <span class="rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-700">Sharing out</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($isSharedIn && $album->facility)
                                    <p class="text-xs font-semibold text-violet-700">From {{ $album->facility->name }}</p>
                                @endif
                                @if($album->event)
                                    <p class="text-xs font-semibold text-teal-700">
                                        <i class="fa-solid fa-calendar-day mr-1"></i>{{ $album->event->title }}
                                    </p>
                                @endif
                                @if($album->description)
                                    <p class="line-clamp-2 text-sm text-slate-600">{{ $album->description }}</p>
                                @endif

                                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                                    <span>{{ $album->visibility_label }}</span>
                                    @if($album->creator)
                                        <span>· by {{ $album->creator->name }}</span>
                                    @endif
                                </div>

                                @if($isOwner)
                                    <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                                        <a href="{{ route('member.galleries.show', $album) }}#upload-photos"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-2.5 py-1.5 text-[11px] font-bold text-white hover:bg-teal-700">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                            Upload photos
                                        </a>
                                        <a href="{{ route('member.galleries.edit', $album) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:border-teal-300 hover:bg-teal-50">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit gallery
                                        </a>
                                        <a href="{{ route('member.galleries.show', $album) }}#manage-photos"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-700 hover:border-teal-300 hover:bg-teal-50">
                                            <i class="fa-solid fa-images"></i>
                                            Manage / delete photos
                                        </a>
                                    </div>
                                @else
                                    <div class="border-t border-slate-100 pt-3">
                                        <a href="{{ route('member.galleries.show', $album) }}"
                                           class="text-xs font-bold text-teal-700 hover:underline">
                                            View gallery →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
