@php
    use App\Models\Gallery;

    $gallery = $gallery ?? null;
    $shareFacilities = $shareFacilities ?? collect();
    $selectedShareIds = collect(old('shared_facility_ids', $gallery?->sharedFacilities?->pluck('id')->all() ?? []));
    $defaultShareScope = old('share_scope', $gallery->share_scope ?? Gallery::SHARE_SCOPE_FACILITY);
    $defaultPublished = (string) old('is_active', isset($gallery) ? ($gallery->is_active ? '1' : '0') : '1');
@endphp

<div class="space-y-5"
     x-data="{ shareScope: @js($defaultShareScope) }">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="title" class="block text-sm font-semibold text-slate-700">Gallery title</label>
            <input type="text" name="title" id="title" required maxlength="255"
                   value="{{ old('title', $gallery->title ?? '') }}"
                   placeholder="e.g. Nurses Week 2026"
                   class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            @php
                $defaultYear = old('year', $gallery->year ?? now()->year);
                $yearOptions = range((int) now()->year + 1, 1990);
            @endphp
            <label for="year" class="block text-sm font-semibold text-slate-700">Year</label>
            <select name="year" id="year" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                @foreach($yearOptions as $yearOption)
                    <option value="{{ $yearOption }}" @selected((int) $defaultYear === (int) $yearOption)>{{ $yearOption }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Used for search and filtering on the galleries page.</p>
            @error('year')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-slate-700">Description <span class="font-normal text-slate-400">(optional)</span></label>
        <textarea name="description" id="description" rows="3" maxlength="5000"
                  class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"
                  placeholder="Short context for this album">{{ old('description', $gallery->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="event_id" class="block text-sm font-semibold text-slate-700">Linked event <span class="font-normal text-slate-400">(optional)</span></label>
        <select name="event_id" id="event_id"
                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            <option value="">No linked event</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}" @selected((string) old('event_id', $gallery->event_id ?? '') === (string) $event->id)>
                    {{ $event->title }}
                    @if($event->event_date)
                        — {{ $event->event_date->format('M j, Y') }}
                    @endif
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Tie this album to a facility or company event when one exists.</p>
        @error('event_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <p class="block text-sm font-semibold text-slate-700">Publish status</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-teal-300">
                <input type="radio" name="is_active" value="1" class="mt-1 text-teal-600 focus:ring-teal-500"
                       @checked($defaultPublished === '1')>
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Published</span>
                    <span class="mt-0.5 block text-xs text-slate-500">Visible to facility members (and shared facilities if enabled).</span>
                </span>
            </label>
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-teal-300">
                <input type="radio" name="is_active" value="0" class="mt-1 text-teal-600 focus:ring-teal-500"
                       @checked($defaultPublished === '0')>
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Unpublished</span>
                    <span class="mt-0.5 block text-xs text-slate-500">Hidden from other viewers until you publish it.</span>
                </span>
            </label>
        </div>
        @error('is_active')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <p class="block text-sm font-semibold text-slate-700">Facility access</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            @foreach(Gallery::shareScopeOptions() as $value => $label)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-teal-300">
                    <input type="radio" name="share_scope" value="{{ $value }}" class="mt-1 text-teal-600 focus:ring-teal-500"
                           x-model="shareScope"
                           @checked($defaultShareScope === $value)>
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">
                            @if($value === Gallery::SHARE_SCOPE_FACILITY)
                                Only {{ $facility->name ?? 'this facility' }} can view.
                            @else
                                Selected facilities get read-only access. Only you can edit.
                            @endif
                        </span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('share_scope')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div x-show="shareScope === '{{ Gallery::SHARE_SCOPE_SHARED }}'" x-cloak>
        <label for="shared_facility_ids" class="block text-sm font-semibold text-slate-700">Share with facilities</label>
        <select name="shared_facility_ids[]" id="shared_facility_ids" multiple
                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"
                size="{{ min(8, max(4, $shareFacilities->count())) }}">
            @forelse($shareFacilities as $shareFacility)
                <option value="{{ $shareFacility->id }}" @selected($selectedShareIds->contains($shareFacility->id))>
                    {{ $shareFacility->name }}
                    @if($shareFacility->city || $shareFacility->state)
                        ({{ collect([$shareFacility->city, $shareFacility->state])->filter()->implode(', ') }})
                    @endif
                </option>
            @empty
                <option value="" disabled>No other facilities available</option>
            @endforelse
        </select>
        <p class="mt-1 text-xs text-slate-500">Hold Ctrl (Windows) or Cmd (Mac) to select multiple facilities. Shared facilities can view only.</p>
        @error('shared_facility_ids')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.content-visibility-field', [
        'visibilityValue' => old('visibility', $gallery->visibility ?? 'both'),
        'visibilityWrapperClass' => '',
        'visibilityLabelClass' => 'block text-sm font-semibold text-slate-700',
        'visibilitySelectClass' => 'mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200',
        'visibilityHelpClass' => 'mt-1 text-xs text-slate-500',
        'visibilityHelp' => 'Website = public facility site. Portal = employee galleries. Both = all surfaces.',
    ])
</div>
