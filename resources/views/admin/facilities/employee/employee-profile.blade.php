<div x-data="{ showPhoneModal: false, showEmailModal: false, phoneAction: '', editPhone: null, addPhone: false, deletePhoneId: null }"
    x-show="tab === 'personal'">
    @php use Illuminate\Support\Facades\Auth; @endphp


    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="POST" action="{{ $isAddMode ? route('admin.employees.store') : route('admin.employees.personal.update', $employee->emp_id ?? '') }}">
            @csrf
            @if(!$isAddMode)
                @method('PUT')
            @endif
            @include('admin.facilities.employee._employee-profile-form')
        </form>
    </div>

    <!-- Email Edit Modal (outside form, only for Edit mode with user_id) -->
    {{-- @if(!$isAddMode && !empty($employee->user_id))
    <div x-show="showEmailModal" style="display: none;" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-black opacity-40" @click="showEmailModal = false"></div>
        <div class="bg-white rounded-lg shadow-lg p-6 z-10 w-full max-w-sm">
            <h2 class="text-lg font-semibold mb-4">Update Email</h2>
            <form method="POST" action="{{ route('admin.employees.email.update', $employee->user_id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ $employee->user_id }}">
                <input type="hidden" name="emp_id" value="{{ $employee->emp_id }}">
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
    @endif --}}
    {{-- @include('admin.facilities.employee.employee-phone-manage') --}}
</div>