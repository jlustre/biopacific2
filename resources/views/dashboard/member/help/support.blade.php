@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ category: '{{ old('category', '') }}', priority: '{{ old('priority', 'normal') }}' }">
    @include('dashboard.member.help.partials.hero', [
        'tone' => 'indigo',
        'heroIcon' => 'fa-laptop-code',
        'heroBadge' => 'IT & Portal Support',
        'heroTitle' => 'Technical Support',
        'heroSubtitle' => 'Get help with portal login, how to use the employee portal, facility websites, document uploads, or bugs and errors. For payroll, benefits, or leave, contact HR instead.',
        'tips' => [
            ['icon' => 'fa-camera', 'title' => 'Add screenshots', 'body' => 'Upload images or PDFs showing error messages or the screen where something went wrong.'],
            ['icon' => 'fa-list-ol', 'title' => 'List the steps', 'body' => 'Tell us what you clicked, what you expected, and what actually happened.'],
            ['icon' => 'fa-book-open', 'title' => 'Check guides first', 'body' => 'User guides and manuals will appear below as they become available.'],
        ],
        'stats' => [
            ['label' => 'Response time', 'value' => '1–2 business days'],
            ['label' => 'For', 'value' => 'Portal · Websites · How-to'],
            ['label' => 'Tracking', 'value' => 'Reference code provided'],
        ],
    ])

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('member.help.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">View my support requests</a>
        <a href="{{ route('member.help.hr') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
            <i class="fa-solid fa-headset text-xs"></i>
            Need Contact HR instead?
        </a>
    </div>

    {{-- User guides & manuals (links filled in later) --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4 sm:px-8">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-indigo-600">Self-service</p>
                    <h2 class="mt-1 text-base font-bold text-slate-900">User guides & manuals</h2>
                    <p class="mt-1 text-sm text-slate-500">Quick references for using the portal. More guides will be added here over time.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-600">Coming soon</span>
            </div>
        </div>
        <div class="grid gap-3 px-6 py-6 sm:grid-cols-3 sm:px-8">
            @forelse(($userGuides ?? []) as $guide)
                @if(!empty($guide['url']))
                    <a href="{{ $guide['url'] }}" target="_blank" rel="noopener"
                       class="rounded-2xl border border-indigo-200 bg-indigo-50/50 p-4 transition hover:border-indigo-400 hover:bg-indigo-50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <i class="fa-solid {{ $guide['icon'] ?? 'fa-book' }}"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900">{{ $guide['title'] }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $guide['description'] }}</p>
                        <p class="mt-3 text-xs font-semibold text-indigo-700">Open guide →</p>
                    </a>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <i class="fa-solid {{ $guide['icon'] ?? 'fa-book' }}"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-800">{{ $guide['title'] }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $guide['description'] }}</p>
                        <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Coming soon</p>
                    </div>
                @endif
            @empty
                <div class="sm:col-span-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    User guides and manuals will be published here soon.
                </div>
            @endforelse
        </div>
    </div>

    @if($errors->any())
    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('member.help.support.store') }}" enctype="multipart/form-data" class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @csrf

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '1',
            'sectionTitle' => 'What technical issue are you reporting?',
            'sectionDescription' => 'Pick the category that best describes your portal or website problem.',
            'accent' => 'indigo',
        ])
        <div class="grid gap-3 px-6 py-6 sm:grid-cols-2 sm:px-8 lg:grid-cols-3">
            @foreach($categories as $value => $category)
            <label class="group cursor-pointer rounded-2xl border p-4 transition"
                   :class="category === '{{ $value }}' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'">
                <input type="radio" name="category" value="{{ $value }}" class="sr-only" x-model="category" @checked(old('category') === $value) required>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                        <i class="fa-solid {{ $category['icon'] }}"></i>
                    </span>
                    <span>
                        <span class="block text-sm font-bold text-slate-900">{{ $category['label'] }}</span>
                        <span class="mt-1 block text-xs leading-relaxed text-slate-500">{{ $category['description'] }}</span>
                    </span>
                </div>
            </label>
            @endforeach
        </div>

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '2',
            'sectionTitle' => 'Priority & contact details',
            'accent' => 'indigo',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Priority</label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                           :class="priority === 'normal' ? 'border-slate-400 bg-slate-50 ring-1 ring-slate-200' : 'border-slate-200'">
                        <input type="radio" name="priority" value="normal" class="mt-1" x-model="priority" @checked(old('priority', 'normal') === 'normal') required>
                        <span>
                            <span class="block text-sm font-bold text-slate-900">Normal</span>
                            <span class="mt-1 block text-xs text-slate-500">Standard queue — best for non-blocking questions.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                           :class="priority === 'urgent' ? 'border-rose-400 bg-rose-50 ring-1 ring-rose-200' : 'border-slate-200'">
                        <input type="radio" name="priority" value="urgent" class="mt-1" x-model="priority" @checked(old('priority') === 'urgent')>
                        <span>
                            <span class="block text-sm font-bold text-rose-800">Urgent</span>
                            <span class="mt-1 block text-xs text-rose-700">Work is blocked — you cannot complete required portal tasks.</span>
                        </span>
                    </label>
                </div>
            </div>

            @if($facilities->count() > 1)
            <div>
                <label for="facility_id" class="mb-1 block text-sm font-semibold text-slate-700">Your facility</label>
                <select name="facility_id" id="facility_id" required class="portal-help-input">
                    <option value="">Select facility…</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @selected((string) old('facility_id', $defaultFacilityId) === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            @elseif($facilities->count() === 1)
            <input type="hidden" name="facility_id" value="{{ $facilities->first()->id }}">
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Full name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $prefillName) }}" required class="portal-help-input">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $prefillEmail) }}" required class="portal-help-input">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $prefillPhone) }}" class="portal-help-input">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Employee ID</label>
                    <input type="text" value="{{ $prefillEmployeeNum ?: '—' }}" disabled class="portal-help-input bg-slate-50 text-slate-500">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="preferred_contact" class="mb-1 block text-sm font-semibold text-slate-700">Preferred response method</label>
                    <select name="preferred_contact" id="preferred_contact" required class="portal-help-input">
                        @foreach($preferredContactOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('preferred_contact', 'email') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="best_time_to_reach" class="mb-1 block text-sm font-semibold text-slate-700">Best time to reach you</label>
                    <select name="best_time_to_reach" id="best_time_to_reach" class="portal-help-input">
                        <option value="">No preference</option>
                        @foreach($bestTimeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('best_time_to_reach') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '3',
            'sectionTitle' => 'Describe the technical issue',
            'sectionDescription' => 'Include error text, the page URL if known, and what you were trying to do.',
            'accent' => 'indigo',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <div>
                <label for="subject" class="mb-1 block text-sm font-semibold text-slate-700">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required class="portal-help-input" placeholder="e.g. Cannot upload certification PDF on Documents page">
            </div>
            <div>
                <label for="message" class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                <textarea name="message" id="message" rows="6" required class="portal-help-input" placeholder="Describe the problem, any error messages, browser used, and what you were trying to do.">{{ old('message') }}</textarea>
            </div>
            <div>
                <label for="steps_to_reproduce" class="mb-1 block text-sm font-semibold text-slate-700">Steps to reproduce <span class="font-normal text-slate-400">(recommended)</span></label>
                <textarea name="steps_to_reproduce" id="steps_to_reproduce" rows="4" class="portal-help-input" placeholder="1. Go to…&#10;2. Click…&#10;3. Expected… / Actual…">{{ old('steps_to_reproduce') }}</textarea>
            </div>
            <div>
                <label for="attachments" class="mb-1 block text-sm font-semibold text-slate-700">Screenshots or attachments <span class="font-normal text-slate-400">(optional)</span></label>
                <p class="mb-2 text-xs text-slate-500">Up to 5 files — images or PDF, 5MB each.</p>
                <input type="file" name="attachments[]" id="attachments" accept="image/*,.pdf" multiple
                       class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
            </div>
        </div>

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '4',
            'sectionTitle' => 'Privacy confirmation',
            'accent' => 'indigo',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <label class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <input type="checkbox" name="no_phi_confirmed" value="1" class="mt-1 rounded border-amber-300 text-indigo-600 focus:ring-indigo-500" @checked(old('no_phi_confirmed')) required>
                <span class="text-sm text-amber-950">
                    I confirm this request does <strong>not</strong> include protected health information (PHI). Attachments and descriptions should relate to portal or website support only.
                </span>
            </label>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Submit to Technical Support
                </button>
                <a href="{{ route('dashboard.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </div>
    </form>
</section>

<style>
.portal-help-input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid rgb(203 213 225);
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
}
.portal-help-input:focus {
    outline: none;
    border-color: rgb(99 102 241);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
</style>
@endsection
