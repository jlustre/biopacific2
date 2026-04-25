<div x-data="{ showPhoneModal: false, showEmailModal: false, phoneAction: '', editPhone: null, addPhone: false, deletePhoneId: null }">
    <div x-show="tab === 'personal'">
        @php use Illuminate\Support\Facades\Auth; @endphp
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form method="POST" action="{{ $isAddMode ? route('admin.employees.store') : route('admin.employees.personal.update', $employee->id ?? '') }}">
                @csrf
                @if(!$isAddMode)
                    @method('PUT')
                @endif
                @include('admin.facilities.employee._employee-profile-form')
            </form>
        </div>
        <!-- Email Edit Modal (outside form, only for Edit mode with user_id) -->
        {{-- ...existing code... --}}
        @include('admin.facilities.employee.employee-phone-manage')
    </div>
</div>