@php
    use Illuminate\Support\Facades\Auth;

    $formatDateInput = static function ($value): string {
        if ($value === null || $value === '') {
            return '';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    };
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    {{-- Employee Id --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Employee ID</label>
        <input type="text" name="employee_num" value="{{ old('employee_num', (isset($employee) && isset($employee->employee_num)) ? $employee->employee_num : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('employee_num') ? 'border-red-500' : 'border border-teal-300' }}"
            required>
        @error('employee_num')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Last Name --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Last Name</label>
        <input type="text" name="last_name" value="{{ old('last_name', (isset($employee) && isset($employee->last_name)) ? $employee->last_name : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('last_name') ? 'border-red-500' : 'border border-teal-300' }}"
            required>
        @error('last_name')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- First Name --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">First Name</label>
        <input type="text" name="first_name" value="{{ old('first_name', (isset($employee) && isset($employee->first_name)) ? $employee->first_name : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('first_name') ? 'border-red-500' : 'border border-teal-300' }}"
            required>
        @error('first_name')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Middle Name --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Middle Name</label>
        <input type="text" name="middle_name" value="{{ old('middle_name', (isset($employee) && isset($employee->middle_name)) ? $employee->middle_name : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('middle_name') ? 'border-red-500' : 'border border-teal-300' }}">
        @error('middle_name')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Social Security Number --}}
    <div class="mb-1" x-data="{ editingSsn: false }">
        <label class="block text-sm font-medium mb-2">SSN</label>
        <template x-if="!editingSsn">
            <div class="flex items-center gap-2">
                <input type="password" value="*************" class="form-input w-full rounded-lg px-2 py-1 border border-teal-300 bg-gray-100" readonly>
                <button type="button" @click="editingSsn = true"
                    class="ml-2 px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700 cursor-pointer">Edit</button>
            </div>
        </template>
        <template x-if="editingSsn">
            <div class="flex items-center gap-2">
                <input type="text" name="ssn" inputmode="numeric" pattern="[0-9]*" maxlength="15"
                    value="{{ old('ssn', (isset($employee) && isset($employee->ssn)) ? $employee->ssn : '') }}"
                    class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('ssn') ? 'border-red-500' : 'border border-teal-300' }}"
                    autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <button type="button" @click="editingSsn = false"
                    class="ml-2 px-2 py-1 text-xs bg-gray-400 text-white rounded hover:bg-gray-500">Cancel</button>
            </div>
        </template>
        @error('ssn')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Date of Birth --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Date of Birth</label>
        <input type="date" name="dob" value="{{ old('dob', isset($employee) ? $formatDateInput($employee->dob ?? null) : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('dob') ? 'border-red-500' : 'border border-teal-300' }}">
        @error('dob')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Gender --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-1">Gender</label>
        <select name="gender"
            class="form-select w-full rounded-lg px-2 py-1 {{ $errors->has('gender') ? 'border-red-500' : 'border border-teal-300' }}">
            <option value="" @if(old('gender', (isset($employee) && isset($employee->gender)) ? $employee->gender : '')=='') selected @endif>-- Select --
            </option>
            <option value="M" @if(old('gender', (isset($employee) && isset($employee->gender)) ? $employee->gender : '')=='M') selected @endif>Male</option>
            <option value="F" @if(old('gender', (isset($employee) && isset($employee->gender)) ? $employee->gender : '')=='F') selected @endif>Female</option>
            <option value="O" @if(old('gender', (isset($employee) && isset($employee->gender)) ? $employee->gender : '')=='O') selected @endif>Other</option>
            <option value="N" @if(old('gender', (isset($employee) && isset($employee->gender)) ? $employee->gender : '')=='N') selected @endif>Prefer not to
                say
            </option>
        </select>
        @error('gender')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Phone --}}
    <div class="mb-1">
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium mr-2">Phone Number</label>
            <button type="button" @click="showPhoneModal = true"
                class="ml-2 px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700">Manage</button>
        </div>
        <select name="phone_id"
            class="form-select w-full border border-teal-300 rounded-lg px-2 py-1 text-sm">
            @if(isset($employee) && isset($employee->phones))
                @forelse($employee->phones as $phone)
                <option value="{{ $phone->phone_id }}" @if(strtoupper((string) $phone->is_primary) === 'Y') selected @endif>
                    {{ ucfirst($phone->phone_type) }}: {{ $phone->phone_number }}@if(strtoupper((string) $phone->is_primary) === 'Y')
                    (Primary)@endif
                </option>
                @empty
                <option disabled selected>No phone numbers on file</option>
                @endforelse
            @endif
        </select>
    </div>
    {{-- Email --}}
    @php
        $employeeEmail = '';
        if (isset($employee)) {
            $employeeEmail = $employee->user?->email ?? $employee->email ?? '';
        }
    @endphp
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Email</label>
        <input type="email" name="email" value="{{ old('email', $employeeEmail) }}"
            class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('email') ? 'border-red-500' : 'border border-teal-300' }}"
            required maxlength="255" autocomplete="email">
        @error('email')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Original Hire Date --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Original Hire Date</label>
        <input type="date" name="original_hire_dt"
            value="{{ old('original_hire_dt', isset($employee) ? $formatDateInput($employee->original_hire_dt ?? null) : '') }}"
            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
    </div>
    {{-- Badge Number --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Badge Number</label>
        <input type="text" name="badge_num" maxlength="50"
            value="{{ old('badge_num', isset($employee) ? ($employee->badge_num ?? '') : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 border border-teal-300">
        @error('badge_num')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Badge Effective Date --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Badge Effective Date</label>
        <input type="date" name="badge_eff_dt"
            value="{{ old('badge_eff_dt', isset($employee) ? $formatDateInput($employee->badge_eff_dt ?? null) : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 border border-teal-300">
        @error('badge_eff_dt')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Union Code --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Union Code</label>
        <input type="text" name="union_code" maxlength="50"
            value="{{ old('union_code', isset($employee) ? ($employee->union_code ?? '') : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 border border-teal-300"
            list="union-code-options">
        <datalist id="union-code-options">
            @foreach(($unionCodeOptions ?? collect()) as $code)
                <option value="{{ $code }}"></option>
            @endforeach
        </datalist>
        @error('union_code')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Effective Date of Membership --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Effective Date of Membership</label>
        <input type="date" name="effdt_of_membership"
            value="{{ old('effdt_of_membership', isset($employee) ? $formatDateInput($employee->effdt_of_membership ?? null) : '') }}"
            class="form-input w-full rounded-lg px-2 py-1 border border-teal-300">
        @error('effdt_of_membership')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- Action --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Action</label>
        <select name="action_id" class="form-select w-full rounded-lg px-2 py-1 border border-teal-300">
            <option value="">-- Select --</option>
            @php $selectedAction = old('action_id', isset($employee) ? ($employee->action_id ?? '') : ''); @endphp
            @foreach(($actionOptions ?? collect()) as $option)
                <option value="{{ $option->id }}" @if((string) $selectedAction === (string) $option->id) selected @endif>{{ $option->name }}</option>
            @endforeach
        </select>
        @error('action_id')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Marital Status --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Marital Status</label>
        <select name="marital_status_id" class="form-select w-full rounded-lg px-2 py-1 border border-teal-300">
            <option value="">-- Select --</option>
            @php $selectedMarital = old('marital_status_id', $employee->marital_status_id ?? ''); @endphp
            @foreach($maritalOptions as $option)
                <option value="{{ $option->id }}" @if($selectedMarital == $option->id) selected @endif>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    {{-- Ethnic Group --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Ethnic Group</label>
        <select name="ethnic_group_id" class="form-select w-full rounded-lg px-2 py-1 border border-teal-300">
            <option value="">-- Select --</option>
            @php $selectedEthnic = old('ethnic_group_id', $employee->ethnic_group_id ?? ''); @endphp
            @foreach($ethnicOptions as $option)
                <option value="{{ $option->id }}" @if($selectedEthnic == $option->id) selected @endif>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    {{-- Military Status --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Military Status</label>
        <select name="military_status_id" class="form-select w-full rounded-lg px-2 py-1 border border-teal-300">
            <option value="">-- Select --</option>
            @php $selectedMilitary = old('military_status_id', $employee->military_status_id ?? ''); @endphp
            @foreach($militaryOptions as $option)
                <option value="{{ $option->id }}" @if($selectedMilitary == $option->id) selected @endif>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    {{-- Citizenship Status --}}
    <div class="mb-1">
        <label class="block text-sm font-medium mb-2">Citizenship Status</label>
        <select name="citizenship_status_id" class="form-select w-full rounded-lg px-2 py-1 border border-teal-300">
            <option value="">-- Select --</option>
            @php $selectedCitizen = old('citizenship_status_id', $employee->citizenship_status_id ?? ''); @endphp
            @foreach($citizenOptions as $option)
                <option value="{{ $option->id }}" @if($selectedCitizen == $option->id) selected @endif>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>


    <div class="mb-1" x-data="{ editingSsn: false }">
        <template x-if="editingSsn">
            <div class="flex items-center gap-2">
                <input type="text" name="ssn" inputmode="numeric" pattern="[0-9]*" maxlength="15"
                    value="{{ old('ssn', (isset($employee) && isset($employee->ssn)) ? $employee->ssn : '') }}"
                    class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('ssn') ? 'border-red-500' : 'border border-teal-300' }}"
                    autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <button type="button" @click="editingSsn = false"
                    class="ml-2 px-2 py-1 text-xs bg-gray-400 text-white rounded hover:bg-gray-500">Cancel</button>
            </div>
        </template>
        @error('ssn')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

</div>
<div class="flex justify-between mt-2 md:col-span-2 lg:col-span-4">
    <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
    <button type="submit"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">Save
        Profile</button>
</div>
