<div x-data="{ showPhoneModal: false, phoneAction: '', editPhone: null, addPhone: false, deletePhoneId: null }">
    <div x-show="tab === 'personal'" x-cloak data-employee-tab-panel="personal">
        @php use Illuminate\Support\Facades\Auth; @endphp
        @php $canEditCoreTabs = $canEditCoreTabs ?? false; @endphp
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @php
                $linkedPortalUser = $employee?->user
                    ?? (($employee?->email ?? null) ? \App\Models\User::query()->where('email', $employee->email)->first() : null);
                $profilePendingHr = $linkedPortalUser
                    && ($linkedPortalUser->profile_hr_status ?? '') === \App\Support\MemberPortal\ProfileHrStatus::PENDING_HR;
            @endphp
            @if($profilePendingHr)
            <div id="profile-hr-review" class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-4">
                <p class="text-sm font-bold text-amber-950">Profile pending HR confirmation</p>
                <p class="mt-1 text-sm text-amber-900">
                    {{ $linkedPortalUser->name }} submitted portal profile details
                    {{ $linkedPortalUser->profile_submitted_at?->diffForHumans() ?? 'recently' }}.
                    Review the information below, then confirm once the official employee record is accurate.
                </p>
                <form method="POST" action="{{ route('admin.employees.profile.confirm-hr', $employee) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Confirm employee profile
                    </button>
                </form>
            </div>
            @endif
            <form method="POST" action="{{ $isAddMode ? route('admin.employees.store') : ($employeeFormRoutes['personal'] ?? route('admin.employees.personal.update', $employee->id ?? '')) }}">
                @csrf
                @if(!$isAddMode)
                    @method('PUT')
                @endif
                @include('admin.facilities.employee._employee-profile-form')
            </form>
        </div>
        @if($canEditCoreTabs)
            @include('admin.facilities.employee.employee-phone-manage')
        @endif
    </div>
</div>