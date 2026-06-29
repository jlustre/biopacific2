@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    @include('dashboard.member.help.partials.hero', [
        'heroIcon' => 'fa-ticket',
        'heroTitle' => 'Submit support request',
        'heroSubtitle' => 'Get help with portal access, documents, training records, employee profile corrections, or technical issues. Attach screenshots when helpful.',
        'tips' => [
            ['icon' => 'fa-camera', 'title' => 'Add screenshots', 'body' => 'Upload images or PDFs showing error messages or the screen where something went wrong.'],
            ['icon' => 'fa-list-ol', 'title' => 'List the steps', 'body' => 'For technical issues, tell us what you clicked and what you expected to happen.'],
            ['icon' => 'fa-bolt', 'title' => 'Mark urgent carefully', 'body' => 'Use urgent priority only when work is blocked and you cannot complete required tasks.'],
        ],
    ])

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('member.help.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">View my help requests</a>
        <a href="{{ route('member.help.hr') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Need HR for payroll or benefits?</a>
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
            'sectionTitle' => 'What kind of support do you need?',
            'sectionDescription' => 'Pick the category that best describes your issue.',
        ])
        <div class="grid gap-3 px-6 py-6 sm:grid-cols-2 sm:px-8 lg:grid-cols-3">
            @foreach($categories as $value => $category)
            <label class="group cursor-pointer rounded-2xl border p-4 transition {{ old('category') === $value ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50' }}">
                <input type="radio" name="category" value="{{ $value }}" class="sr-only category-radio" @checked(old('category') === $value) required onchange="toggleTechnicalSection()">
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
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Priority</label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 {{ old('priority', 'normal') === 'normal' ? 'border-slate-400 bg-slate-50 ring-1 ring-slate-200' : 'border-slate-200' }}">
                        <input type="radio" name="priority" value="normal" class="mt-1" @checked(old('priority', 'normal') === 'normal') required>
                        <span>
                            <span class="block text-sm font-bold text-slate-900">Normal</span>
                            <span class="mt-1 block text-xs text-slate-500">Standard queue — best for non-blocking questions.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 {{ old('priority') === 'urgent' ? 'border-rose-400 bg-rose-50 ring-1 ring-rose-200' : 'border-slate-200' }}">
                        <input type="radio" name="priority" value="urgent" class="mt-1" @checked(old('priority') === 'urgent')>
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
            'sectionTitle' => 'Describe the issue',
            'sectionDescription' => 'Tell us what happened and what you need resolved.',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <div>
                <label for="subject" class="mb-1 block text-sm font-semibold text-slate-700">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required class="portal-help-input" placeholder="Short summary of the support need">
            </div>
            <div>
                <label for="message" class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                <textarea name="message" id="message" rows="6" required class="portal-help-input" placeholder="Describe the problem, any error messages, and what you were trying to do.">{{ old('message') }}</textarea>
            </div>
            <div id="technical-section" class="{{ old('category') === 'technical' ? '' : 'hidden' }}">
                <label for="steps_to_reproduce" class="mb-1 block text-sm font-semibold text-slate-700">Steps to reproduce <span class="font-normal text-slate-400">(recommended for technical issues)</span></label>
                <textarea name="steps_to_reproduce" id="steps_to_reproduce" rows="4" class="portal-help-input" placeholder="1. Go to…&#10;2. Click…&#10;3. Expected… / Actual…">{{ old('steps_to_reproduce') }}</textarea>
            </div>
            <div>
                <label for="attachments" class="mb-1 block text-sm font-semibold text-slate-700">Attachments <span class="font-normal text-slate-400">(optional)</span></label>
                <p class="mb-2 text-xs text-slate-500">Up to 5 files — images or PDF, 5MB each.</p>
                <input type="file" name="attachments[]" id="attachments" accept="image/*,.pdf" multiple
                       class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
            </div>
        </div>

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '4',
            'sectionTitle' => 'Privacy confirmation',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <label class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <input type="checkbox" name="no_phi_confirmed" value="1" class="mt-1 rounded border-amber-300 text-indigo-600 focus:ring-indigo-500" @checked(old('no_phi_confirmed')) required>
                <span class="text-sm text-amber-950">
                    I confirm this request does <strong>not</strong> include protected health information (PHI). Attachments and descriptions should relate to portal or employment support only.
                </span>
            </label>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Submit support request
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
<script>
function toggleTechnicalSection() {
    const selected = document.querySelector('input[name="category"]:checked');
    const section = document.getElementById('technical-section');
    if (!selected || !section) return;
    section.classList.toggle('hidden', selected.value !== 'technical');
}
document.addEventListener('DOMContentLoaded', toggleTechnicalSection);
</script>
@endsection
