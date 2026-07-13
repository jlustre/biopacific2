@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ category: '{{ old('category', '') }}' }">
    @include('dashboard.member.help.partials.hero', [
        'tone' => 'teal',
        'heroIcon' => 'fa-headset',
        'heroBadge' => 'Human Resources',
        'heroTitle' => 'Contact HR',
        'heroSubtitle' => 'Reach the HR team about payroll, benefits, time off, onboarding, employee records, or company policies. Use Technical Support for portal or website problems.',
        'tips' => [
            ['icon' => 'fa-receipt', 'title' => 'Include pay period dates', 'body' => 'For payroll questions, mention the check date or pay period so HR can locate your record quickly.'],
            ['icon' => 'fa-shield-heart', 'title' => 'Employment matters only', 'body' => 'This form is for HR and employment topics — not clinical, resident, or patient health information.'],
            ['icon' => 'fa-clock', 'title' => 'Tell us how to reach you', 'body' => 'Choose email or phone and your preferred time window for a follow-up.'],
        ],
        'stats' => [
            ['label' => 'Response time', 'value' => '1–2 business days'],
            ['label' => 'For', 'value' => 'Payroll · Benefits · Leave'],
            ['label' => 'Tracking', 'value' => 'Reference code provided'],
        ],
    ])

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('member.help.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">View my HR & help requests</a>
        <a href="{{ route('member.help.support') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
            <i class="fa-solid fa-laptop-code text-xs"></i>
            Need Technical Support instead?
        </a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wide text-teal-700">Payroll</p>
            <p class="mt-1 text-xs text-slate-600">Pay, deposits, deductions</p>
        </div>
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wide text-teal-700">Benefits</p>
            <p class="mt-1 text-xs text-slate-600">Insurance & retirement</p>
        </div>
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wide text-teal-700">Time off</p>
            <p class="mt-1 text-xs text-slate-600">PTO, leave, attendance</p>
        </div>
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wide text-teal-700">Records</p>
            <p class="mt-1 text-xs text-slate-600">Profile & employment data</p>
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

    <form method="POST" action="{{ route('member.help.hr.store') }}" class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @csrf

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '1',
            'sectionTitle' => 'What is your HR question about?',
            'sectionDescription' => 'Choose the HR topic that best matches your request.',
        ])
        <div class="grid gap-3 px-6 py-6 sm:grid-cols-2 sm:px-8 lg:grid-cols-3">
            @foreach($categories as $value => $category)
            <label class="group cursor-pointer rounded-2xl border p-4 transition"
                   :class="category === '{{ $value }}' ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200' : 'border-slate-200 hover:border-teal-300 hover:bg-slate-50'">
                <input type="radio" name="category" value="{{ $value }}" class="sr-only" x-model="category" @checked(old('category') === $value) required>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700 group-hover:bg-teal-200">
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
            'sectionTitle' => 'Your contact information',
            'sectionDescription' => 'HR will use these details to respond to your inquiry.',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
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
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $prefillPhone) }}" class="portal-help-input" placeholder="(555) 555-5555">
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
            'sectionTitle' => 'Describe your HR question',
            'sectionDescription' => 'Provide enough detail for HR to research and respond accurately.',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <div>
                <label for="subject" class="mb-1 block text-sm font-semibold text-slate-700">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required class="portal-help-input" placeholder="e.g. Direct deposit update for March pay period">
            </div>
            <div>
                <label for="message" class="mb-1 block text-sm font-semibold text-slate-700">Message</label>
                <textarea name="message" id="message" rows="7" required class="portal-help-input" placeholder="Explain your question, include relevant dates, and mention any documents you have already submitted.">{{ old('message') }}</textarea>
            </div>
        </div>

        @include('dashboard.member.help.partials.section-header', [
            'sectionNumber' => '4',
            'sectionTitle' => 'Privacy confirmation',
        ])
        <div class="space-y-4 px-6 py-6 sm:px-8">
            <label class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <input type="checkbox" name="no_phi_confirmed" value="1" class="mt-1 rounded border-amber-300 text-teal-600 focus:ring-teal-500" @checked(old('no_phi_confirmed')) required>
                <span class="text-sm text-amber-950">
                    I confirm this message does <strong>not</strong> contain protected health information (PHI) about residents, patients, or clients. I understand HR will use this form for employment-related communication only.
                </span>
            </label>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-teal-700">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Send to HR
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
    line-height: 1.25rem;
}
.portal-help-input:focus {
    outline: none;
    border-color: rgb(20 184 166);
    box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15);
}
</style>
@endsection
