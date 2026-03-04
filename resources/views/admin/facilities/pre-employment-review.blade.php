@extends('layouts.dashboard')

@section('content')
@php
$sections = [
['key' => 'personal', 'icon' => 'fas fa-user', 'label' => 'Personal Information'],
['key' => 'position', 'icon' => 'fas fa-briefcase', 'label' => 'Position Desired'],
['key' => 'drivers-license', 'icon' => 'fas fa-id-card', 'label' => "Driver's License"],
['key' => 'referral-source', 'icon' => 'fas fa-bullhorn', 'label' => 'Referral Source'],
['key' => 'work-auth', 'icon' => 'fas fa-passport', 'label' => 'Work Authorization'],
['key' => 'work-experience', 'icon' => 'fas fa-history', 'label' => 'Work Experience'],
['key' => 'education', 'icon' => 'fas fa-graduation-cap', 'label' => 'Education'],
['key' => 'addresses', 'icon' => 'fas fa-map-marker-alt', 'label' => 'Previous Addresses'],
];
@endphp

<x-admin.pre-employment-review.layout :back-url="route('admin.facility.hiring', $facility)" back-label="Back to Hiring">
    <x-slot:header>
        <x-admin.pre-employment-review.header-card :application="$application" />
    </x-slot:header>

    <x-slot:sidebar>
        <h2 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-list mr-2"></i>Form Sections
        </h2>
        <nav class="space-y-2">
            @foreach($sections as $section)
            <x-admin.pre-employment-review.nav-item :section="$section['key']" :icon="$section['icon']"
                :label="$section['label']" />
            @endforeach
        </nav>
    </x-slot:sidebar>

    <x-slot:actions>
        <x-admin.pre-employment-review.status-actions :facility="$facility" :application="$application" />
    </x-slot:actions>

    <div>
        <x-admin.pre-employment-review.section-card section="personal" icon="fas fa-user" title="Personal Information">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-admin.pre-employment-review.field label="First Name" :value="$application->first_name" />
                <x-admin.pre-employment-review.field label="Last Name" :value="$application->last_name" />
                <x-admin.pre-employment-review.field label="Middle Name" :value="$application->middle_name" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <x-admin.pre-employment-review.field label="Phone Number" :value="$application->phone_number" />
                <x-admin.pre-employment-review.field label="Email" :value="$application->email" class="md:col-span-2" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <x-admin.pre-employment-review.field label="County" :value="$application->county" />
                <x-admin.pre-employment-review.field label="Current Address" :value="$application->current_address"
                    class="md:col-span-2" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <x-admin.pre-employment-review.field label="City" :value="$application->city" />
                <x-admin.pre-employment-review.field label="State" :value="$application->state" />
                <x-admin.pre-employment-review.field label="Zip Code" :value="$application->zip_code" />
            </div>
        </x-admin.pre-employment-review.section-card>

        <x-admin.pre-employment-review.section-card section="position" icon="fas fa-briefcase" title="Position Desired">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.pre-employment-review.field label="Position Applied For"
                    :value="$application->position_applied_for" />
                <x-admin.pre-employment-review.field label="Employment Type"
                    :value="ucfirst($application->employment_type ?? 'N/A')" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <x-admin.pre-employment-review.field label="Shift Preference"
                    :value="$application->shift_preference ?? 'N/A'" />
                <x-admin.pre-employment-review.field label="Date Available"
                    :value="$application->date_available ? \Carbon\Carbon::parse($application->date_available)->format('M d, Y') : null" />
                <x-admin.pre-employment-review.field label="Wage/Salary Expected"
                    :value="$application->wage_salary_expected" />
            </div>
            <hr class="my-6" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <x-admin.pre-employment-review.field label="Have you ever worked for this company?"
                    :value="$application->worked_here_before ? 'Yes' : 'No'" />
                <x-admin.pre-employment-review.field label="If yes, when and where?"
                    :value="$application->worked_here_when_where ?? 'N/A'" />
                <x-admin.pre-employment-review.field label="Have you ever applied to this company?"
                    :value="$application->applied_here_before ? 'Yes' : 'No'" />
                <x-admin.pre-employment-review.field label="If yes, when and where?"
                    :value="$application->applied_here_when_where ?? 'N/A'" />
                <x-admin.pre-employment-review.field label="Do you have any relatives who work for this company?"
                    :value="$application->relatives_work_here ? 'Yes' : 'No'" />
                <x-admin.pre-employment-review.field label="Please identify"
                    :value="$application->relatives_details ?? 'N/A'" />
            </div>
        </x-admin.pre-employment-review.section-card>

        <x-admin.pre-employment-review.section-card section="drivers-license" icon="fas fa-id-card"
            title="Driver's License">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-admin.pre-employment-review.field label="License Number"
                    :value="$application->drivers_license_number" />
                <x-admin.pre-employment-review.field label="State" :value="$application->drivers_license_state" />
                <x-admin.pre-employment-review.field label="Expiration Date"
                    :value="$application->drivers_license_expiration ? \Carbon\Carbon::parse($application->drivers_license_expiration)->format('M d, Y') : null" />
            </div>
            <div class="mt-6">
                <x-admin.pre-employment-review.field label="Has Driver's License"
                    :value="$application->has_drivers_license ? 'Yes' : 'No'" />
            </div>
        </x-admin.pre-employment-review.section-card>

        <x-admin.pre-employment-review.section-card section="referral-source" icon="fas fa-bullhorn"
            title="Referral Source">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.pre-employment-review.field label="How Did You Hear About Us?"
                    :value="$application->how_heard_about_us" />
                <x-admin.pre-employment-review.field label="If Other, please specify"
                    :value="$application->how_heard_other" />
            </div>
        </x-admin.pre-employment-review.section-card>


        <x-admin.pre-employment-review.section-card section="work-auth" icon="fas fa-passport"
            title="Work Authorization">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.pre-employment-review.field label="Authorized to Work in USA"
                    :value="$application->authorized_to_work_usa ? 'Yes' : 'No'" />
                <x-admin.pre-employment-review.field label="Can Contact Current Employer"
                    :value="$application->contact_current_employer ? 'Yes' : 'No'" />
            </div>
        </x-admin.pre-employment-review.section-card>

        <x-admin.pre-employment-review.section-card section="work-experience" icon="fas fa-history"
            title="Work Experience">
            @if($application->work_experience && !empty($application->work_experience))
            <div class="space-y-4">
                @foreach($application->work_experience as $experience)
                @php
                $startPosition = $experience['start_position'] ?? $experience['position'] ?? null;
                $company = $experience['company'] ?? $experience['employer'] ?? null;
                $from = $experience['start_date'] ?? null;
                $to = $experience['end_date'] ?? null;
                $dates = $from || $to ? (($from ?? 'N/A') . ' - ' . ($to ?? 'Present')) : ($experience['dates'] ??
                null);
                $endPosition = $experience['end_position'] ?? null;
                $supervisor = $experience['supervisor'] ?? ($experience['supervisor_name_title'] ?? null);
                $phone = $experience['phone'] ?? null;
                $eligible = isset($experience['eligible_for_rehire']) ? ($experience['eligible_for_rehire'] ? 'Yes' :
                'No') : null;
                $reason = $experience['reason'] ?? ($experience['reason_for_leaving'] ?? null);
                @endphp
                <x-admin.pre-employment-review.list-entry :title="$company">
                    @if($startPosition)
                    <div><strong>Starting Position:</strong> {{ $startPosition }}</div>
                    @endif
                    @if($endPosition)
                    <div><strong>Ending Position:</strong> {{ $endPosition }}</div>
                    @endif
                    @if($dates)
                    <div><strong>From - To:</strong> {{ $dates }}</div>
                    @endif
                    @if($supervisor)
                    <div><strong>Supervisor:</strong> {{ $supervisor }}</div>
                    @endif
                    @if($phone)
                    <div><strong>Phone:</strong> {{ $phone }}</div>
                    @endif
                    @if($eligible !== null)
                    <div><strong>Eligible for Rehire:</strong> {{ $eligible }}</div>
                    @endif
                    @if($reason)
                    <div><strong>Reason for Leaving:</strong> {{ $reason }}</div>
                    @endif
                </x-admin.pre-employment-review.list-entry>
                @endforeach
            </div>
            @else
            <p class="text-gray-500">No work experience entries found.</p>
            @endif
        </x-admin.pre-employment-review.section-card>

        <x-admin.pre-employment-review.section-card section="education" icon="fas fa-graduation-cap" title="Education">
            {{-- DEBUG: Dump the education array to diagnose why it is empty --}}
            <pre class="bg-yellow-100 text-xs p-2 rounded mb-4">@php var_dump($application->education); @endphp</pre>
            @if($application->education && !empty($application->education))
            <div class="space-y-4">
                @foreach($application->education as $education)
                <x-admin.pre-employment-review.list-entry :title="$education['school'] ?? 'N/A'">
                    @if(!empty($education['level']))
                    <div><strong>Level:</strong> {{ $education['level'] }}</div>
                    @endif
                    @if(!empty($education['date_from']) || !empty($education['date_to']))
                    <div><strong>From - To:</strong> {{ ($education['date_from'] ?? 'N/A') . ' - ' .
                        ($education['date_to'] ?? 'N/A') }}</div>
                    @endif
                    @if(!empty($education['degree']))
                    <div><strong>Degree:</strong> {{ $education['degree'] }}</div>
                    @endif
                    @if(!empty($education['major']))
                    <div><strong>Major:</strong> {{ $education['major'] }}</div>
                    @endif
                    @if(!empty($education['honors']))
                    <div><strong>Honors:</strong> {{ $education['honors'] }}</div>
                    @endif
                    @if(!empty($education['city']))
                    <div><strong>City:</strong> {{ $education['city'] }}</div>
                    @endif
                    @if(!empty($education['state']))
                    <div><strong>State:</strong> {{ $education['state'] }}</div>
                    @endif
                    @if(!empty($education['gpa']))
                    <div><strong>GPA:</strong> {{ $education['gpa'] }}</div>
                    @endif
                    @if(!empty($education['notes']))
                    <div><strong>Notes:</strong> {{ $education['notes'] }}</div>
                    @endif
                    @if(!empty($education['completed']))
                    <div><strong>Completed:</strong> {{ $education['completed'] }}</div>
                    @endif
                    @if(!empty($education['country']))
                    <div><strong>Country:</strong> {{ $education['country'] }}</div>
                    @endif
                    {{-- Display any additional fields dynamically --}}
                    @foreach($education as $key => $value)
                    @if(!in_array($key,
                    ['school','level','date_from','date_to','degree','major','honors','city','state','gpa','notes','completed','country'])
                    && !empty($value))
                    <div><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                    @endif
                    @endforeach
                </x-admin.pre-employment-review.list-entry>
                @endforeach
            </div>
            @else
            <p class="text-gray-500">No education entries found.</p>
            @endif
        </x-admin.pre-employment-review.section-card>

        <!-- Activity History -->
        <x-admin.pre-employment-review.activity-history :application="$application" />
    </div>
</x-admin.pre-employment-review.layout>
@endsection