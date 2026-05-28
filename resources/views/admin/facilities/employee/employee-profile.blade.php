<div x-data="{ showPhoneModal: false, phoneAction: '', editPhone: null, addPhone: false, deletePhoneId: null }">
    <div x-show="tab === 'personal'" x-cloak data-employee-tab-panel="personal">
        @php use Illuminate\Support\Facades\Auth; @endphp
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form method="POST" action="{{ $isAddMode ? route('admin.employees.store') : ($employeeFormRoutes['personal'] ?? route('admin.employees.personal.update', $employee->id ?? '')) }}">
                @csrf
                @if(!$isAddMode)
                    @method('PUT')
                @endif
                @include('admin.facilities.employee._employee-profile-form')
            </form>
        </div>
        @include('admin.facilities.employee.employee-phone-manage')
    </div>
</div>