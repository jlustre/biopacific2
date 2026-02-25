<div x-data="{ open: true }" class="border-l-4 border-teal-500 bg-white p-6 mb-6">
    <button @click="open = !open" class="font-bold text-lg flex items-center mb-2 focus:outline-none">
        <span>EMPLOYMENT APPLICATION</span>
        <span class="ml-2 text-sm">(Click to <span x-text="open ? 'collapse' : 'expand'"></span>)</span>
        <svg :class="{'rotate-180': open}" class="w-5 h-5 ml-2 transition-transform" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" class="mt-2">
        <strong>Almaden Healthcare and Rehabilitation Center</strong> (the "Company") is an equal opportunity and
        affirmative action employer committed to diversifying its workforce. It is the Company's policy to provide equal
        employment opportunities to all employees and applicants without regard to race, color, creed, religion, sex,
        sexual orientation, gender identity or expression, national origin, ancestry, age, mental or physical
        disability, genetic information, marital status, familial status, sexual orientation, military or veteran status
        or any other legally protected status under applicable law or similar factors that are job-related. No question
        on the application is intended to secure information about these subjects. We encourage all qualified
        individuals to apply for employment. We also provide reasonable accommodation to qualified individuals with
        disabilities to complete with the Americans with Disabilities Act and applicable state and local law. If you
        require assistance or a reasonable accommodation to complete the application or any aspect of the application
        process, please contact the Human Resources Department or the hiring manager.
    </div>
</div>

<form method="POST" action="{{ route('employee-application.store') }}" class="space-y-6" id="application-form"
    data-form-saved="true">
    @csrf

    <!-- PERSONAL INFORMATION -->
    <div id="personal-info">
        <button type="button" data-toggle-section="personal-info"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Personal Information</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 pt-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name"
                        value="{{ old('last_name', $preEmployment?->last_name ?? $jobApplication?->last_name ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('last_name') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name"
                        value="{{ old('first_name', $preEmployment?->first_name ?? $jobApplication?->first_name ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('first_name') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Middle Name
                    </label>
                    <input type="text" name="middle_name"
                        value="{{ old('middle_name', $preEmployment?->middle_name ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-6 mt-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone_number"
                        value="{{ old('phone_number', $preEmployment?->phone_number ?? $jobApplication?->phone ?? optional($employee?->currentPhone)->phone_number ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('phone_number') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email"
                        value="{{ old('email', $preEmployment?->email ?? $jobApplication?->email ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('email') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-6 mt-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Current Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="current_address"
                        value="{{ old('current_address', $preEmployment?->current_address ?? optional($employee?->currentAddress)->current_address ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('current_address') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('current_address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-6 mt-4 pb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        City <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="city"
                        value="{{ old('city', $preEmployment?->city ?? optional($employee?->currentAddress)->city ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('city') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('city')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        State <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="state"
                        value="{{ old('state', $preEmployment?->state ?? optional($employee?->currentAddress)->state ?? '') }}"
                        maxlength="2"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('state') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('state')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Zip Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="zip_code"
                        value="{{ old('zip_code', $preEmployment?->zip_code ?? optional($employee?->currentAddress)->zip_code ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('zip_code') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('zip_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        County
                    </label>
                    <input type="text" name="county"
                        value="{{ old('county', $preEmployment?->county ?? optional($employee?->currentAddress)->county ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                </div>
            </div>
        </div>
    </div>

    <!-- POSITION DESIRED -->
    <div id="position-desired">
        <button type="button" data-toggle-section="position-desired"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Position Desired</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 pt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Position Applied For <span class="text-red-500">*</span>
                </label>
                <select name="position_applied_for"
                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('position_applied_for') border-red-500 @else border-gray-300 @enderror"
                    @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    <option value="">-- Select a Position --</option>
                    @foreach($positions as $position)
                    <option value="{{ $position->id }}" @if(old('position_applied_for', $preEmployment?->position_id ??
                        $selectedPositionId ?? $employee?->position_applied_for) == $position->id) selected @endif>
                        {{ $position->title }}
                    </option>
                    @endforeach
                </select>
                @error('position_applied_for')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-6 mt-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Employment Type
                    </label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="employment_type" value="full_time" {{ old('employment_type',
                                $preEmployment?->employment_type ?? $employee?->employment_type ?? '') === 'full_time' ?
                            'checked' : '' }}
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Full Time</span>
                        </label>
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="employment_type" value="part_time" {{ old('employment_type',
                                $preEmployment?->employment_type ?? $employee?->employment_type ?? '') === 'part_time' ?
                            'checked' : '' }}
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Part Time</span>
                        </label>
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="employment_type" value="temporary" {{ old('employment_type',
                                $preEmployment?->employment_type ?? $employee?->employment_type ?? '') === 'temporary' ?
                            'checked' : '' }}
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Temporary</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="employment_type" value="other" {{ old('employment_type',
                                $preEmployment?->employment_type ?? $employee?->employment_type ?? '') === 'other' ?
                            'checked' :
                            '' }}
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Other</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        If Other, specify:
                    </label>
                    <input type="text" name="employment_type_other"
                        value="{{ old('employment_type_other', $preEmployment?->employment_type_other ?? $employee?->employment_type_other ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 mt-4 pb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Shift Preference
                    </label>
                    <select name="shift_preference"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                        <option value="">Select...</option>
                        <option value="day" {{ old('shift_preference', $preEmployment?->shift_preference ??
                            $employee?->shift_preference ?? '') === 'day' ?
                            'selected'
                            : '' }}>Day</option>
                        <option value="evening" {{ old('shift_preference', $preEmployment?->shift_preference ??
                            $employee?->shift_preference ?? '') === 'evening' ?
                            'selected' : '' }}>Evening</option>
                        <option value="weekend" {{ old('shift_preference', $preEmployment?->shift_preference ??
                            $employee?->shift_preference ?? '') === 'weekend' ?
                            'selected' : '' }}>Weekend</option>
                        <option value="any" {{ old('shift_preference', $preEmployment?->shift_preference ??
                            $employee?->shift_preference ?? '') === 'any' ?
                            'selected'
                            : '' }}>Any</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Date Available to Start
                    </label>
                    <input type="date" name="date_available"
                        value="{{ old('date_available', $preEmployment?->date_available?->format('Y-m-d') ?? $employee?->date_available?->format('Y-m-d') ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('date_available')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Wage/Salary Expected
                    </label>
                    <input type="text" name="wage_salary_expected"
                        value="{{ old('wage_salary_expected', $preEmployment?->wage_salary_expected ?? $employee?->wage_salary_expected ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('wage_salary_expected')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @php
            $workedHereBefore = old('worked_here_before', $preEmployment?->worked_here_before ??
            $employee?->worked_here_before ?? false) ? true : false;
            $relativesWorkHere = old('relatives_work_here', $preEmployment?->relatives_work_here ??
            $employee?->relatives_work_here ?? false) ? true : false;
            @endphp
            <div class="px-6 space-y-4 pt-4"
                x-data="{ workedHereBefore: {{ $workedHereBefore ? 'true' : 'false' }}, appliedHereBefore: {{ ($preEmployment?->applied_here_before ?? false) ? 'true' : 'false' }}, relativesWorkHere: {{ $relativesWorkHere ? 'true' : 'false' }} }">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Have You Ever Worked For This Company?
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="worked_here_before" value="1" {{ old('worked_here_before',
                                $preEmployment?->worked_here_before ?? $employee?->worked_here_before ?? false) ?
                            'checked' : ''
                            }}
                            @change="workedHereBefore = true"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="worked_here_before" value="0" {{ !old('worked_here_before',
                                $preEmployment?->worked_here_before ?? $employee?->worked_here_before ?? false) ?
                            'checked' : ''
                            }}
                            @change="workedHereBefore = false"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                </div>

                <div x-show="workedHereBefore" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        If Yes, When and Where?
                    </label>
                    <textarea name="worked_here_when_where" rows="2"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('worked_here_when_where') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled
                        @endif>{{ old('worked_here_when_where', $preEmployment?->worked_here_when_where ?? $employee?->worked_here_when_where ?? '') }}</textarea>
                    @error('worked_here_when_where')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Have You Ever Applied To This Company?
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="applied_here_before" value="1" {{ old('applied_here_before',
                                $preEmployment?->applied_here_before ?? false) ?
                            'checked' : ''
                            }}
                            @change="appliedHereBefore = true"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="applied_here_before" value="0" {{ !old('applied_here_before',
                                $preEmployment?->applied_here_before ?? false) ?
                            'checked' : '' }}
                            @change="appliedHereBefore = false"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                </div>

                <div x-show="appliedHereBefore" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        If Yes, When And Where?
                    </label>
                    <textarea name="applied_here_when_where" rows="2"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('applied_here_when_where') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled
                        @endif>{{ old('applied_here_when_where', $preEmployment?->applied_here_when_where ?? '') }}</textarea>
                    @error('applied_here_when_where')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Do You Have Any Relatives Who Work For The Company?
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="relatives_work_here" value="1" {{ old('relatives_work_here',
                                $preEmployment?->relatives_work_here ?? $employee?->relatives_work_here ?? false) ?
                            'checked' :
                            '' }}
                            @change="relativesWorkHere = true"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="relatives_work_here" value="0" {{ !old('relatives_work_here',
                                $preEmployment?->relatives_work_here ?? $employee?->relatives_work_here ?? false) ?
                            'checked' : '' }}
                            @change="relativesWorkHere = false"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                </div>

                <div class="pb-4" x-show="relativesWorkHere" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        If Yes, Please Specify Name and Relationship:
                    </label>
                    <textarea name="relatives_details" rows="2"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('relatives_details') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled
                        @endif>{{ old('relatives_details', $preEmployment?->relatives_details ?? $employee?->relatives_details ?? '') }}</textarea>
                    @error('relatives_details')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- PREVIOUS EMPLOYMENT -->
    {{-- <div id="previous-employment">
        <button type="button" data-toggle-section="previous-employment"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Previous Employment</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>

    </div> --}}

    <!-- DRIVER'S LICENSE -->
    <div id="drivers-license">
        <button type="button" data-toggle-section="drivers-license"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Driver's License</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            @php
            $hasDriversLicense = old('has_drivers_license', $preEmployment?->has_drivers_license ??
            $employee?->has_drivers_license ?? false) ? true : false;
            @endphp
            <div class="px-6 space-y-4 pt-4 pb-4"
                x-data="{ hasDriversLicense: {{ $hasDriversLicense ? 'true' : 'false' }} }">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Do you have a valid driver's license?<br />
                        <span class="text-xs text-gray-500">(Required where driving a vehicle is an essential
                            funtion)</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="has_drivers_license" value="1" {{ old('has_drivers_license',
                                $preEmployment?->has_drivers_license ?? $employee?->has_drivers_license ?? false) ?
                            'checked' :
                            '' }}
                            @change="hasDriversLicense = true"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="has_drivers_license" value="0" {{ !old('has_drivers_license',
                                $preEmployment?->has_drivers_license ?? $employee?->has_drivers_license ?? false) ?
                            'checked' : '' }}
                            @change="hasDriversLicense = false"
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                </div>

                <div x-show="hasDriversLicense" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Driver's License Number
                    </label>
                    <input type="text" name="drivers_license_number"
                        value="{{ old('drivers_license_number', $preEmployment?->drivers_license_number ?? $employee?->drivers_license_number ?? '') }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('drivers_license_number') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('drivers_license_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="hasDriversLicense" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Driver's License State
                    </label>
                    <select name="drivers_license_state"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('drivers_license_state') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                        <option value="">Select a state</option>
                        @php
                        $selectedState = old('drivers_license_state', $preEmployment?->drivers_license_state ?? '');
                        $states = [
                        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
                        'DC' => 'District of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii',
                        'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
                        'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine',
                        'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
                        'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska',
                        'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico',
                        'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
                        'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
                        'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas',
                        'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington',
                        'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
                        'AS' => 'American Samoa', 'GU' => 'Guam', 'MP' => 'Northern Mariana Islands',
                        'PR' => 'Puerto Rico', 'VI' => 'U.S. Virgin Islands'
                        ];
                        @endphp
                        @foreach($states as $abbr => $name)
                        <option value="{{ $abbr }}" {{ $selectedState===$abbr ? 'selected' : '' }}>
                            {{ $abbr }} - {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    @error('drivers_license_state')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="hasDriversLicense" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Driver's License Expiration
                    </label>
                    <input type="date" name="drivers_license_expiration"
                        value="{{ old('drivers_license_expiration', optional($preEmployment?->drivers_license_expiration)->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('drivers_license_expiration') border-red-500 @else border-gray-300 @enderror"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                    @error('drivers_license_expiration')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- HOW DID YOU HEAR ABOUT US -->
    <div id="referral-source">
        <button type="button" data-toggle-section="referral-source"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Referral Source</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 space-y-4 pt-4 pb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        How Did You Hear About Us?
                    </label>
                    <select name="how_heard_about_us"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                        <option value="">Select...</option>
                        <option value="newspaper" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') ===
                            'newspaper' ? 'selected' : '' }}>Newspaper Ad</option>
                        <option value="internet" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') ===
                            'internet' ? 'selected' : '' }}>Internet Ad</option>
                        <option value="school" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') === 'school' ?
                            'selected' : '' }}>School Recruiting</option>
                        <option value="job_fair" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') ===
                            'job_fair' ? 'selected' : '' }}>Job Fair</option>
                        <option value="agency" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') === 'agency' ?
                            'selected' : '' }}>Employment Agency</option>
                        <option value="referral" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') ===
                            'referral' ? 'selected' : '' }}>Employee Referral</option>
                        <option value="walk_in" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') === 'walk_in'
                            ? 'selected' : '' }}>Walk-in</option>
                        <option value="cna_program" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') ===
                            'cna_program' ? 'selected' : '' }}>CNA Program</option>
                        <option value="other" {{ old('how_heard_about_us', $preEmployment?->how_heard_about_us ??
                            $employee?->how_heard_about_us ?? '') === 'other' ?
                            'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        If Other, please specify:
                    </label>
                    <input type="text" name="how_heard_other"
                        value="{{ old('how_heard_other', $preEmployment?->how_heard_other ?? $employee?->how_heard_other ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                </div>
            </div>
        </div>
    </div>

    <!-- WORK AUTHORIZATION -->
    <div id="work-authorization">
        <button type="button" data-toggle-section="work-authorization"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Work Authorization</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 space-y-4 pt-4 pb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Are You Legally Authorized to Work in the USA? <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="authorized_to_work_usa" value="1" {{ old('authorized_to_work_usa',
                                $preEmployment?->authorized_to_work_usa ?? $employee?->authorized_to_work_usa ?? false)
                            ?
                            'checked' : '' }}
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="authorized_to_work_usa" value="0" {{
                                !old('authorized_to_work_usa', $preEmployment?->authorized_to_work_usa ??
                            $employee?->authorized_to_work_usa ?? false) ?
                            'checked' : '' }}
                            class="text-teal-600 focus:ring-teal-500"
                            @if($status !== 'draft' && $status !== 'returned') disabled @endif>
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        To comply with the Immigration Reform And Control Act, if you are hired, you will be required to
                        provide documents to establish your identity and authorization to work in the USA.
                        Such documents will be required within the first three (3) business days following your hire or
                        upon your first work day if your employment will be less than three (3) business days.
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        If employed by {{ $jobApplication?->jobOpening?->facility?->name ?? 'the Company' }}, you will
                        be subject to its Employee Handbook, Code of
                        Conduct, Employment Dispute Resolution Program, and all policies and procedures.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- WORK EXPERIENCE -->
    <div id="work-experience">
        <button type="button" data-toggle-section="work-experience"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Work Experience (Most Recent First)</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 space-y-6 pt-4 pb-4">
                @for($i = 1; $i <= 3; $i++) <div class="border border-gray-300 rounded-lg p-4">
                    <div class="text-xs font-semibold text-gray-700 mb-4">Position {{ $i }}</div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Name and Address of Employer
                            </label>
                            <textarea name="work_exp_{{ $i }}_employer" rows="3"
                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft'
                                && $status !=='returned' ) disabled
                                @endif>{{ old("work_exp_{$i}_employer", $preEmployment?->work_experience[$i-1]['employer'] ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Starting Position
                            </label>
                            <textarea name="work_exp_{{ $i }}_start_position" rows="3"
                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft'
                                && $status !=='returned' ) disabled
                                @endif>{{ old("work_exp_{$i}_start_position", $preEmployment?->work_experience[$i-1]['start_position'] ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Ending Position
                            </label>
                            <textarea name="work_exp_{{ $i }}_end_position" rows="3"
                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft'
                                && $status !=='returned' ) disabled
                                @endif>{{ old("work_exp_{$i}_end_position", $preEmployment?->work_experience[$i-1]['end_position'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                From Mo__Yr__ To Mo__Yr__<br>
                                Phone Number / Area Code ( )
                            </label>
                            <input type="text" name="work_exp_{{ $i }}_dates" placeholder="Mo__Yr__ To Mo__Yr__"
                                value="{{ old(" work_exp_{$i}_dates", $preEmployment?->work_experience[$i-1]['dates'] ??
                            '') }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm mb-2" @if($status !=='draft'
                            &&
                            $status !=='returned' ) disabled @endif>
                            <input type="tel" name="work_exp_{{ $i }}_phone" placeholder="(  )  -" value="{{ old("
                                work_exp_{$i}_phone", $preEmployment?->work_experience[$i-1]['phone'] ?? '') }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Name & Title of Supervisor
                            </label>
                            <textarea name="work_exp_{{ $i }}_supervisor" rows="3"
                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft'
                                && $status !=='returned' ) disabled
                                @endif>{{ old("work_exp_{$i}_supervisor", $preEmployment?->work_experience[$i-1]['supervisor'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Reason for Leaving
                            </label>
                            <textarea name="work_exp_{{ $i }}_reason" rows="2"
                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft'
                                && $status !=='returned' ) disabled
                                @endif>{{ old("work_exp_{$i}_reason", $preEmployment?->work_experience[$i-1]['reason'] ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Eligible for rehire? ☐ Yes ☐ No
                            </label>
                            <div class="flex items-center space-x-4 h-24 content-center">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="work_exp_{{ $i }}_rehire" value="yes"
                                        class="text-teal-600 focus:ring-teal-500" {{ old("work_exp_{$i}_rehire",
                                        $preEmployment?->work_experience[$i-1]['rehire'] ?? '') === 'yes' ? 'checked' :
                                    '' }}
                                    @if($status !=='draft' && $status
                                    !=='returned' ) disabled @endif>
                                    <span class="ml-2 text-sm">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="work_exp_{{ $i }}_rehire" value="no"
                                        class="text-teal-600 focus:ring-teal-500" {{ old("work_exp_{$i}_rehire",
                                        $preEmployment?->work_experience[$i-1]['rehire'] ?? '') === 'no' ? 'checked' :
                                    '' }}
                                    @if($status !=='draft' && $status
                                    !=='returned' ) disabled @endif>
                                    <span class="ml-2 text-sm">No</span>
                                </label>
                            </div>
                        </div>
                    </div>
            </div>
            @endfor
        </div>

        <!-- ADDITIONAL WORK INFORMATION -->
        <div class="px-6 space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    May we contact your current employer listed above? <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center">
                        <input type="radio" name="contact_current_employer" value="1"
                            class="text-teal-600 focus:ring-teal-500" {{ (old('contact_current_employer',
                            $preEmployment?->contact_current_employer ?? $employee?->contact_current_employer ?? null))
                        ?
                        'checked' : '' }}
                        @if($status !=='draft' && $status !=='returned' )
                        disabled @endif>
                        <span class="ml-2">YES</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="contact_current_employer" value="0"
                            class="text-teal-600 focus:ring-teal-500" {{ !(old('contact_current_employer',
                            $preEmployment?->contact_current_employer ?? $employee?->contact_current_employer ?? null))
                        ?
                        'checked' : '' }}
                        @if($status !=='draft' && $status !=='returned' )
                        disabled @endif>
                        <span class="ml-2">NO</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Use this space to describe any previous work history and/or detail particular job responsibilities
                    listed above that you believe are important or should be considered. Include any additional
                    information
                    that you feel maybe be relevant to the job for which you are applying. <span
                        class="text-red-500">*</span>
                </label>
                <textarea name="work_history_description" rows="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                    @if($status !=='draft' && $status !=='returned' ) disabled
                    @endif>{{ old('work_history_description', $preEmployment?->work_history_description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    List additional references, including address and telephone <span class="text-red-500">*</span>
                </label>
                <textarea name="additional_references" rows="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                    @if($status !=='draft' && $status !=='returned' ) disabled
                    @endif>{{ old('additional_references', $preEmployment?->additional_references ?? '') }}</textarea>
            </div>
        </div>
    </div>
    </div> <!-- End of WORK EXPERIENCE -->

    <!-- PREVIOUS ADDRESS FOR THE PAST 7 YEARS -->
    <div id="previous-addresses">
        <button type="button" data-toggle-section="previous-addresses"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Previous Address for the Past 7 Years (Most Recent
                First)</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 py-4 space-y-4 pt-4">
                @for($i = 1; $i <= 7; $i++) <div class="border border-gray-300 rounded-md p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                {{ $i }}. Previous Address
                            </label>
                            <input type="text" name="prev_address_{{ $i }}_address" value="{{ old("
                                prev_address_{$i}_address", $preEmployment?->previous_addresses[$i-1]['address'] ?? '')
                            }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Phone Number
                            </label>
                            <input type="tel" name="prev_address_{{ $i }}_phone" value="{{ old("
                                prev_address_{$i}_phone", $preEmployment?->previous_addresses[$i-1]['phone'] ?? '') }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                City
                            </label>
                            <input type="text" name="prev_address_{{ $i }}_city" value="{{ old("
                                prev_address_{$i}_city", $preEmployment?->previous_addresses[$i-1]['city'] ?? '') }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                State
                            </label>
                            <input type="text" name="prev_address_{{ $i }}_state" maxlength="2" value="{{ old("
                                prev_address_{$i}_state", $preEmployment?->previous_addresses[$i-1]['state'] ?? '') }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Zip Code
                            </label>
                            <input type="text" name="prev_address_{{ $i }}_zip" value="{{ old(" prev_address_{$i}_zip",
                                $preEmployment?->previous_addresses[$i-1]['zip'] ?? '') }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                County
                            </label>
                            <input type="text" name="prev_address_{{ $i }}_county" value="{{ old("
                                prev_address_{$i}_county", $preEmployment?->previous_addresses[$i-1]['county'] ?? '')
                            }}"
                            class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status !=='draft' &&
                            $status !=='returned' ) disabled @endif>
                        </div>
                    </div>
            </div>
            @endfor
        </div>
    </div>
    </div>

    <!-- RECORD OF EDUCATION -->
    <div id="record-education">
        <button type="button" data-toggle-section="record-education"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Record of Education</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 py-4 space-y-6 pt-4">
                @php
                $educationLevels = [
                'High School (Last Attended)',
                'Colleges/Universities',
                'Graduate School',
                'Other (Business, Technical, Secretarial, etc.)',
                ];

                // Reorganize education data by level for easier form prefilling
                $educationByLevel = [];
                foreach ($educationLevels as $idx => $levelName) {
                $educationByLevel[$idx] = [];
                }

                if ($preEmployment?->education) {
                foreach ($preEmployment->education as $edu) {
                $levelIndex = array_search($edu['level'], $educationLevels);
                if ($levelIndex !== false) {
                $educationByLevel[$levelIndex][] = $edu;
                }
                }
                }
                @endphp

                @foreach($educationLevels as $index => $level)
                <div class="border border-gray-300 rounded-md overflow-hidden">
                    <div class="bg-gray-100 px-4 py-2 font-semibold text-sm text-gray-900 border-b border-gray-300">
                        {{ $level }}
                    </div>

                    <div class="p-4 space-y-4">
                        @for($entry = 1; $entry <= 2; $entry++) <div
                            class="border-b border-gray-200 pb-4{{ $entry === 2 ? ' pb-0' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        Name and Address of School(s)
                                    </label>
                                    <textarea name="education_{{ $index }}_{{ $entry }}_school" rows="2"
                                        class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status
                                        !=='draft' && $status !=='returned' ) disabled
                                        @endif>{{ old("education_{$index}_{$entry}_school", $educationByLevel[$index][$entry-1]['school'] ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        Dates Attended<br>From<br>Mo./Yr.
                                    </label>
                                    <input type="text" name="education_{{ $index }}_{{ $entry }}_from"
                                        placeholder="Mo./Yr." value="{{ old(" education_{$index}_{$entry}_from",
                                        $educationByLevel[$index][$entry-1]['date_from'] ?? '' ) }}"
                                        class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status
                                        !=='draft' && $status !=='returned' ) disabled @endif>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        To<br>Mo./Yr.
                                    </label>
                                    <input type="text" name="education_{{ $index }}_{{ $entry }}_to"
                                        placeholder="Mo./Yr." value="{{ old(" education_{$index}_{$entry}_to",
                                        $educationByLevel[$index][$entry-1]['date_to'] ?? '' ) }}"
                                        class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status
                                        !=='draft' && $status !=='returned' ) disabled @endif>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        Graduated<br><span class="text-xs">Yes / No</span>
                                    </label>
                                    <div class="flex gap-2 mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="education_{{ $index }}_{{ $entry }}_graduated"
                                                value="yes" class="text-teal-600 focus:ring-teal-500" {{
                                                old("education_{$index}_{$entry}_graduated",
                                                $educationByLevel[$index][$entry-1]['graduated'] ?? '' )==='yes'
                                                ? 'checked' : '' }} @if($status !=='draft' && $status !=='returned' )
                                                disabled @endif>
                                            <span class="ml-1 text-xs">Yes</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="education_{{ $index }}_{{ $entry }}_graduated"
                                                value="no" class="text-teal-600 focus:ring-teal-500" {{
                                                old("education_{$index}_{$entry}_graduated",
                                                $educationByLevel[$index][$entry-1]['graduated'] ?? '' )==='no'
                                                ? 'checked' : '' }} @if($status !=='draft' && $status !=='returned' )
                                                disabled @endif>
                                            <span class="ml-1 text-xs">No</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        Type of<br>Degree/Diploma<br>Received or<br>Expected
                                    </label>
                                    <input type="text" name="education_{{ $index }}_{{ $entry }}_degree" value="{{ old("
                                        education_{$index}_{$entry}_degree",
                                        $educationByLevel[$index][$entry-1]['degree'] ?? '' ) }}"
                                        class="w-full px-2 py-2 border border-gray-300 rounded text-sm" @if($status
                                        !=='draft' && $status !=='returned' ) disabled @endif>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Major/Minor Fields of Study
                                </label>
                                <input type="text" name="education_{{ $index }}_{{ $entry }}_major" value="{{ old("
                                    education_{$index}_{$entry}_major", $educationByLevel[$index][$entry-1]['major']
                                    ?? '' ) }}" class="w-full px-2 py-2 border border-gray-300 rounded text-sm"
                                    @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                            </div>
                    </div>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>
    </div>

    <!-- PROFESSIONAL AFFILIATIONS AND ACCREDITATIONS -->
    <div id="professional-affiliations">
        <button type="button" data-toggle-section="professional-affiliations"
            class="w-full bg-gray-50 px-6 py-4 border-l-4 border-teal-600 mt-6 text-left hover:bg-gray-100 transition flex items-center justify-between cursor-pointer">
            <h4 class="text-lg font-bold text-gray-900 uppercase">Professional Affiliations and Accreditations</h4>
            <span class="section-toggle"><i class="fas fa-chevron-up"></i></span>
        </button>
        <div class="section-content">
            <div class="px-6 py-4 space-y-6 pt-4 pb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Please list any professional affiliations or accreditations that have a direct bearing upon your
                        qualification for the job for which you are applying. Include all licenses and certifications.
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea name="professional_affiliations" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled
                        @endif>{{ old('professional_affiliations', $preEmployment?->professional_affiliations ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Have you ever had your profession license or certification suspended, revoked or restricted?
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-6 mb-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="license_suspended" value="1"
                                class="text-teal-600 focus:ring-teal-500" {{ (old('license_suspended',
                                $preEmployment?->license_suspended ?? null)) ? 'checked' : '' }}
                            @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="license_suspended" value="0"
                                class="text-teal-600 focus:ring-teal-500" {{ !(old('license_suspended',
                                $preEmployment?->license_suspended ?? null)) ? 'checked' : '' }}
                            @if($status !=='draft' && $status !=='returned' ) disabled @endif>
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        If yes, please explain:
                    </label>
                    <textarea name="license_suspended_explanation" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled
                        @endif>{{ old('license_suspended_explanation', $preEmployment?->license_suspended_explanation ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Do you have any special skills or abilities that directly relate to the job for which you are
                        applying?
                    </label>
                    <textarea name="special_skills" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        @if($status !=='draft' && $status !=='returned' ) disabled
                        @endif>{{ old('special_skills', $preEmployment?->special_skills ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    @if($status === 'draft' || $status === 'returned')
    <div class="pt-6 px-6 border-t border-gray-200">
        <input type="hidden" name="action" id="form-action" value="save">

        <div class="flex gap-3 mb-3" id="button-container">
            <button type="submit" id="save-btn" disabled
                class="px-6 py-3 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700 hover:disabled:bg-gray-400 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                Save Application Form
            </button>

            <button type="submit" id="submit-btn" disabled
                class="px-6 py-3 rounded-lg text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 hover:disabled:bg-gray-400 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 transition flex items-center gap-2">
                <i class="fas fa-paper-plane"></i>
                Submit to Hiring Manager
            </button>
        </div>

        <div id="submitted-message" class="hidden text-red-600 text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            This application has been submitted to the hiring manager and can no longer be edited.
        </div>
    </div>
    @elseif($status === 'submitted')
    <div class="pt-6 px-6 border-t border-gray-200">
        <div class="text-red-600 text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            This application has been submitted to the hiring manager and can no longer be edited.
        </div>
    </div>
    @endif
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission buttons
    const saveBtn = document.getElementById('save-btn');
    const submitBtn = document.getElementById('submit-btn');
    const formAction = document.getElementById('form-action');
    const buttonContainer = document.getElementById('button-container');
    const submittedMessage = document.getElementById('submitted-message');
    const form = document.querySelector('form[action*="employee-application"]');
    
    console.log('Form elements found:', {
        saveBtn: !!saveBtn,
        submitBtn: !!submitBtn,
        formAction: !!formAction,
        form: !!form
    });

    // ===== FORM STATE MANAGEMENT =====
    // Track if form has been saved at least once in this session
    let formHasBeenSaved = sessionStorage.getItem('formSaved') === 'true';
    let formInitialValues = {};
    let formIsDirty = false;

    function getInputValue(input) {
        if (input.type === 'checkbox' || input.type === 'radio') {
            return input.checked ? '1' : '0';
        }
        if (input.multiple) {
            return Array.from(input.selectedOptions).map(option => option.value).join('|');
        }
        return input.value ?? '';
    }

    // Store initial form values per field
    function storeInitialFormValues() {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (!input.name) {
                return;
            }
            input.dataset.initialValue = getInputValue(input);
        });
    }

    // Check if form has changed from initial values
    function checkFormDirty() {
        const inputs = form.querySelectorAll('input, textarea, select');
        let isDirty = false;

        inputs.forEach(input => {
            if (!input.name) {
                return;
            }
            const currentValue = getInputValue(input);
            const initialValue = input.dataset.initialValue ?? '';

            if (currentValue !== initialValue) {
                isDirty = true;
            }
        });

        return isDirty;
    }

    // Update button states based on form state
    function updateButtonStates() {
        formIsDirty = checkFormDirty();
        
        // Save button: enabled if form is dirty
        if (saveBtn) {
            saveBtn.disabled = !formIsDirty;
            if (formIsDirty) {
                saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Submit button: enabled only if form has been saved AND not dirty
        if (submitBtn) {
            submitBtn.disabled = !formHasBeenSaved || formIsDirty;
            if (!formHasBeenSaved || formIsDirty) {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        console.log('Button states updated:', {
            formIsDirty: formIsDirty,
            formHasBeenSaved: formHasBeenSaved,
            saveBtnDisabled: saveBtn?.disabled,
            submitBtnDisabled: submitBtn?.disabled
        });
    }

    // Initialize form state
    if (form) {
        storeInitialFormValues();
        
        // Add change listeners to all form inputs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', updateButtonStates);
            input.addEventListener('input', updateButtonStates);
        });
        
        // Update button states on page load
        updateButtonStates();
        
        // Listen for form submit to mark as saved
        form.addEventListener('submit', function(e) {
            const action = formAction?.value;
            
            // Only mark as saved when save button is clicked (action == 'save')
            if (action === 'save') {
                formHasBeenSaved = true;
                sessionStorage.setItem('formSaved', 'true');
                
                // After successful save, re-store the initial values
                setTimeout(() => {
                    storeInitialFormValues();
                    updateButtonStates();
                }, 100);
            }
        });
    }
    
    // ===== END FORM STATE MANAGEMENT =====
    
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            console.log('Save button clicked');
            formAction.value = 'save';
            // Form will be marked as saved in the form submit listener
        });
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Submit button clicked');
            formAction.value = 'submit';
            
            // Confirm submission
            if (confirm('Are you sure you want to submit this application to the hiring manager? You will not be able to edit it after submission.')) {
                console.log('User confirmed submission');
                // Disable buttons and show submitted message
                if (saveBtn) saveBtn.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
                if (saveBtn) saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                if (submitBtn) submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                if (submittedMessage) {
                    submittedMessage.classList.remove('hidden');
                }
                
                // Submit the form
                if (form) {
                    console.log('Submitting form...');
                    form.submit();
                } else {
                    console.error('Form not found!');
                }
            } else {
                console.log('User cancelled submission');
            }
        });
    }
    
    // Add form submit listener to log when form actually submits
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form is submitting with action:', formAction.value);
        });
    }
    
    // Collapsible sections
    const sectionIds = [
        'personal-info',
        'position-desired',
        // 'previous-employment',
        'drivers-license',
        'referral-source',
        'work-authorization',
        'work-experience',
        'previous-addresses',
        'record-education',
        'professional-affiliations'
    ];

    // Map fields to their sections for error handling
    const fieldToSectionMap = {
        'first_name': 'personal-info',
        'middle_name': 'personal-info',
        'last_name': 'personal-info',
        'email': 'personal-info',
        'phone_number': 'personal-info',
        'current_address': 'personal-info',
        'city': 'personal-info',
        'state': 'personal-info',
        'zip_code': 'personal-info',
        'county': 'personal-info',
        'position_applied_for': 'position-desired',
        'employment_type': 'position-desired',
        'employment_type_other': 'position-desired',
        'shift_preference': 'position-desired',
        'date_available': 'position-desired',
        'wage_salary_expected': 'position-desired',
        // 'worked_here_before': 'previous-employment',
        // 'worked_here_when_where': 'previous-employment',
        // 'relatives_work_here': 'previous-employment',
        // 'relatives_details': 'previous-employment',
        'has_drivers_license': 'drivers-license',
        'drivers_license_number': 'drivers-license',
        'drivers_license_state': 'drivers-license',
        'drivers_license_expiration': 'drivers-license',
        'how_heard_about_us': 'referral-source',
        'how_heard_other': 'referral-source',
        'authorized_to_work_usa': 'work-authorization',
        'contact_current_employer': 'work-authorization',
        'work_history_description': 'work-authorization'
    };

    // Check for errors and auto-expand sections with errors
    const errorFields = document.querySelectorAll('.text-red-600');
    const sectionsWithErrors = new Set();
    
    errorFields.forEach(errorElement => {
        // Find the associated input/select/textarea
        const inputGroup = errorElement.closest('div');
        if (inputGroup) {
            const input = inputGroup.querySelector('input, select, textarea');
            if (input) {
                const fieldName = input.getAttribute('name');
                if (fieldName && fieldToSectionMap[fieldName]) {
                    sectionsWithErrors.add(fieldToSectionMap[fieldName]);
                }
            }
        }
    });

    // Load saved state from localStorage and override for sections with errors
    sectionIds.forEach(sectionId => {
        const section = document.getElementById(sectionId);
        const content = section ? section.querySelector('.section-content') : null;
        const toggle = section ? section.querySelector('.section-toggle') : null;
        
        if (content && toggle) {
            // If section has errors, force it open
            const hasErrors = sectionsWithErrors.has(sectionId);
            const isOpen = hasErrors || localStorage.getItem(`form-section-${sectionId}`) !== 'false';
            
            if (isOpen) {
                content.classList.remove('hidden');
                toggle.innerHTML = '<i class="fas fa-chevron-up"></i>';
                if (hasErrors) {
                    localStorage.setItem(`form-section-${sectionId}`, 'true');
                }
            } else {
                content.classList.add('hidden');
                toggle.innerHTML = '<i class="fas fa-chevron-down"></i>';
            }
        }
    });

    // Scroll to first error if any
    if (errorFields.length > 0) {
        setTimeout(() => {
            const firstError = errorFields[0];
            const firstErrorField = firstError.previousElementSibling;
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
        }, 300);
    }

    // Add click handlers for toggle buttons
    document.querySelectorAll('[data-toggle-section]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-toggle-section');
            const content = document.getElementById(sectionId).querySelector('.section-content');
            const toggle = this.querySelector('.section-toggle');
            
            const isOpen = !content.classList.contains('hidden');
            
            if (isOpen) {
                content.classList.add('hidden');
                toggle.innerHTML = '<i class="fas fa-chevron-down"></i>';
                localStorage.setItem(`form-section-${sectionId}`, 'false');
            } else {
                content.classList.remove('hidden');
                toggle.innerHTML = '<i class="fas fa-chevron-up"></i>';
                localStorage.setItem(`form-section-${sectionId}`, 'true');
            }
        });
    });
});
</script>