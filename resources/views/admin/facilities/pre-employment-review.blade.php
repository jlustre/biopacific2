@extends('layouts.dashboard')

@section('content')
@php
// Define collapsible forms and their sections
$forms = [
[
'key' => 'application',
'label' => 'Application Form',
'sections' => [
['key' => 'personal', 'icon' => 'fas fa-user', 'label' => 'Personal Information'],
['key' => 'position', 'icon' => 'fas fa-briefcase', 'label' => 'Position Desired'],
['key' => 'drivers-license', 'icon' => 'fas fa-id-card', 'label' => "Driver's License"],
['key' => 'referral-source', 'icon' => 'fas fa-bullhorn', 'label' => 'Referral Source'],
['key' => 'work-auth', 'icon' => 'fas fa-passport', 'label' => 'Work Authorization'],
['key' => 'work-experience', 'icon' => 'fas fa-history', 'label' => 'Work Experience'],
['key' => 'education', 'icon' => 'fas fa-graduation-cap', 'label' => 'Education'],
['key' => 'addresses', 'icon' => 'fas fa-map-marker-alt', 'label' => 'Previous Addresses'],
['key' => 'others', 'icon' => 'fas fa-ellipsis-h', 'label' => 'Others'],
],
],
[
'key' => 'confidential_reference',
'label' => 'Confidential Reference Check',
'sections' => [], // Add sections as needed
],
[
'key' => 'license_verification',
'label' => 'License or Certification Verification',
'sections' => [],
],
[
'key' => 'applicant_flow',
'label' => 'Applicant Flow Data',
'sections' => [],
],
[
'key' => 'substance_abuse',
'label' => 'Substance Abuse Policy',
'sections' => [],
],
[
'key' => 'applicant_disclosure',
'label' => 'Applicant Disclosure',
'sections' => [],
],
[
'key' => 'notice_background',
'label' => 'Notice For Background Checks',
'sections' => [],
],
];
@endphp

<x-admin.pre-employment-review.layout :back-url="route('admin.facility.hiring', $facility)" back-label="Back to Hiring">
    <x-slot:header>
        <x-admin.pre-employment-review.header-card :application="$application" />
    </x-slot:header>

    <x-slot:sidebar>
        <div x-data="{ openForm: 'application' }">
            @foreach($forms as $form)
            <div class="mb-2">
                <button type="button"
                    @click="openForm === '{{ $form['key'] }}' ? openForm = null : openForm = '{{ $form['key'] }}'"
                    class="w-full flex items-center gap-2 px-4 py-2 rounded-lg font-bold text-gray-900 hover:bg-gray-100 transition"
                    :class="{ 'bg-teal-50': openForm === '{{ $form['key'] }}' }">
                    <i class="fas fa-folder mr-2"></i> {{ $form['label'] }}
                    <i class="fas"
                        :class="openForm === '{{ $form['key'] }}' ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <div x-show="openForm === '{{ $form['key'] }}'" x-transition class="pl-4 mt-1">
                    @foreach($form['sections'] as $section)
                    <x-admin.pre-employment-review.nav-item :section="$section['key']" :icon="$section['icon']"
                        :label="$section['label']" />
                    @endforeach
                    @if(empty($form['sections']))
                    <div class="text-gray-500 text-xs italic px-2 py-1">No sections yet</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
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

        <x-admin.pre-employment-review.section-card section="others" icon="fas fa-ellipsis-h" title="Other Information">
            @php
            // Collect fields not shown in other sections
            $shownKeys = [
            'first_name','last_name','middle_name','phone_number','email','county','current_address','city','state','zip_code',
            'position_applied_for','employment_type','shift_preference','date_available','wage_salary_expected','worked_here_before','worked_here_when_where','applied_here_before','applied_here_when_where','relatives_work_here','relatives_details',
            'drivers_license_number','drivers_license_state','drivers_license_expiration','has_drivers_license',
            'how_heard_about_us','how_heard_other',
            'authorized_to_work_usa','contact_current_employer',
            'work_experience','education','previous_addresses',
            ];
            @endphp
            @php $otherFields = collect($application->getAttributes())->except($shownKeys)->filter(); @endphp
            @if($otherFields->isNotEmpty())
            @foreach($otherFields as $key => $value)
            <div class="mb-2"><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
            @endforeach
            @else
            <p class="text-gray-500">No other information found.</p>
            @endif
        </x-admin.pre-employment-review.section-card>
        <x-admin.pre-employment-review.section-card section="addresses" icon="fas fa-map-marker-alt"
            title="Previous Addresses">
            @if($application->previous_addresses && !empty($application->previous_addresses))
            <div class="space-y-4">
                @foreach($application->previous_addresses as $address)
                <x-admin.pre-employment-review.list-entry :title="$address['address'] ?? ($address['street'] ?? 'N/A')">
                    @php
                    $ordered = [
                    'address' => 'Address',
                    'city' => 'City',
                    'state' => 'State',
                    'zip' => 'Zip',
                    'county' => 'County',
                    'phone' => 'Phone',
                    ];
                    @endphp
                    @foreach($ordered as $key => $label)
                    @if(!empty($address[$key]))
                    <div><strong>{{ $label }}:</strong> {{ $address[$key] }}</div>
                    @endif
                    @endforeach
                    {{-- Display any additional fields not in the standard order --}}
                    @foreach($address as $key => $value)
                    @if(!array_key_exists($key, $ordered) && !empty($value))
                    <div><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                    @endif
                    @endforeach
                </x-admin.pre-employment-review.list-entry>
                @endforeach
            </div>
            @else
            <p class="text-gray-500">No previous addresses found.</p>
            @endif
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

        <!-- Button Blocks/Card (moved below Activity History) -->
        <x-admin.pre-employment-review.status-actions :facility="$facility" :application="$application" />
    </div>
</x-admin.pre-employment-review.layout>
@endsection