<div x-data="{ showPhoneModal: false, showEmailModal: false, phoneAction: '', editPhone: null, addPhone: false, deletePhoneId: null }"
    x-show="tab === 'personal'">
    @php use Illuminate\Support\Facades\Auth; @endphp

    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="POST" action="{{ route('admin.employees.personal.update', $employee->employee_num) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-2">User ID</label>
                    <input type="text" name="user_id" value="{{ old('user_id', $employee->user_id) }}"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 bg-gray-100" readonly>
                </div>
                <div class="mb-1" x-data="{ editingEmpId: false }">
                    <label class="block text-sm font-medium mb-2">Employee ID</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="employee_num" value="{{ old('employee_num', $employee->employee_num) }}"
                            class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 bg-gray-100"
                            :readonly="!editingEmpId">
                        @php
                        $user = Auth::user();
                        $canShowEmpIdBtn = false;
                        if ($user && method_exists($user, 'hasRole')) {
                        $canShowEmpIdBtn = $user->hasRole('hrrd') || $user->hasRole('admin');
                        }
                        @endphp
                        @if($canShowEmpIdBtn)
                        <button type="button" @click="editingEmpId = true"
                            class="ml-2 px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700 cursor-pointer">Edit</button>
                        @endif
                    </div>
                </div>
                <div class="mb-1" x-data="{ editingSsn: false }">
                    <label class="block text-sm font-medium mb-2">SSN</label>
                    @php
                    $user = Auth::user();
                    $canEditSsn = false;
                    if ($user && method_exists($user, 'hasRole')) {
                    $canEditSsn = $user->hasRole('hrrd') || $user->hasRole('admin') || ($user->id ==
                    $employee->user_id);
                    }
                    $maskedSsn = $employee->ssn ? str_repeat('*', max(0, strlen($employee->ssn) - 4)) .
                    substr($employee->ssn, -4) : '';
                    @endphp
                    <template x-if="!editingSsn">
                        <div class="flex items-center gap-2">
                            <input type="text" value="{{ $maskedSsn }}"
                                class="form-input w-full rounded-lg px-2 py-1 border border-teal-300 bg-gray-100"
                                readonly>
                            @if($canEditSsn)
                            <button type="button" @click="editingSsn = true"
                                class="ml-2 px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700 cursor-pointer">Edit</button>
                            @endif
                        </div>
                    </template>
                    <template x-if="editingSsn">
                        <div class="flex items-center gap-2">
                            <input type="text" name="ssn" inputmode="numeric" pattern="[0-9]*" maxlength="15"
                                value="{{ old('ssn', $employee->ssn) }}"
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
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-2">Original Hire Date</label>
                    <input type="date" name="original_hire_dt"
                        value="{{ old('original_hire_dt', $employee->original_hire_dt) }}"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                </div>
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-2">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                        class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('last_name') ? 'border-red-500' : 'border border-teal-300' }}"
                        required>
                    @error('last_name')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-2">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}"
                        class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('first_name') ? 'border-red-500' : 'border border-teal-300' }}"
                        required>
                    @error('first_name')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-2">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}"
                        class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('middle_name') ? 'border-red-500' : 'border border-teal-300' }}">
                    @error('middle_name')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-2">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $employee->dob) }}"
                        class="form-input w-full rounded-lg px-2 py-1 {{ $errors->has('dob') ? 'border-red-500' : 'border border-teal-300' }}">
                    @error('dob')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-1">
                    <label class="block text-sm font-medium mb-1">Gender</label>
                    <select name="gender"
                        class="form-select w-full rounded-lg px-2 py-1 {{ $errors->has('gender') ? 'border-red-500' : 'border border-teal-300' }}">
                        <option value="" @if(old('gender', $employee->gender)=='') selected @endif>-- Select --
                        </option>
                        <option value="M" @if(old('gender', $employee->gender)=='M') selected @endif>Male</option>
                        <option value="F" @if(old('gender', $employee->gender)=='F') selected @endif>Female</option>
                        <option value="O" @if(old('gender', $employee->gender)=='O') selected @endif>Other</option>
                        <option value="N" @if(old('gender', $employee->gender)=='N') selected @endif>Prefer not to
                            say
                        </option>
                    </select>
                    @error('gender')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-1">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium mr-2">Phone Number</label>
                        <button type="button" @click="showPhoneModal = true"
                            class="ml-2 px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700">Manage</button>
                    </div>
                    <select name="phone_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1 text-sm">
                        @forelse($employee->phones as $phone)
                        <option value="{{ $phone->phone_id }}" @if($phone->is_primary) selected @endif>
                            {{ ucfirst($phone->phone_type) }}: {{ $phone->phone_number }}@if($phone->is_primary)
                            (Primary)@endif
                        </option>
                        @empty
                        <option disabled selected>No phone numbers on file</option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-1" x-data="{ editingEmail: false }">
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <div class="flex items-center gap-2">
                        <input type="email" value="{{ $employee->user ? $employee->user->email : '' }}"
                            class="form-input w-full rounded-lg px-2 py-1 border border-teal-300 bg-gray-100" readonly>
                        <button type="button" @click="showEmailModal = true"
                            class="ml-2 px-2 py-1 text-xs bg-teal-600 text-white rounded hover:bg-teal-700 cursor-pointer">Edit</button>
                    </div>
                </div>
                @error('email')
                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>


            <div class="flex justify-between mt-2 md:col-span-2 lg:col-span-4">
                <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">Save
                    Profile</button>
            </div>
        </form>
    </div>
    <!-- Email Edit Modal (outside form) -->
    @if($employee->user_id)
    <div x-show="showEmailModal" style="display: none;" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-black opacity-40" @click="showEmailModal = false"></div>
        <div class="bg-white rounded-lg shadow-lg p-6 z-10 w-full max-w-sm">
            <h2 class="text-lg font-semibold mb-4">Update Email</h2>
            <form method="POST" action="{{ route('admin.employees.email.update', $employee->user_id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ $employee->user_id }}">
                <input type="hidden" name="employee_num" value="{{ $employee->employee_num }}">
                <input type="email" name="email" inputmode="email" maxlength="255"
                    value="{{ $employee->user ? $employee->user->email : '' }}"
                    class="form-input w-full rounded-lg px-2 py-1 mb-4 {{ $errors->has('email') ? 'border-red-500' : 'border border-teal-300' }}"
                    autocomplete="off" required>
                @error('email')
                <div class="text-red-600 text-xs mt-1 mb-2">{{ $message }}</div>
                @enderror
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showEmailModal = false"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                        onclick="return confirm('Changing the email will affect the user\'s login credentials. The user will need to use the new email to log in.\n\nAre you sure you want to continue?');">Save</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    </div>
    @include('admin.facilities.employee.employee-phone-manage')
</div>