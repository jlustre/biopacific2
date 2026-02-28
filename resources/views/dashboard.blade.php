@extends(($readOnly ?? false) ? 'layouts.dashboard' : 'layouts.user_dashboard', ['title' => (($readOnly ?? false) ?
$viewingUser->name . '\'s Dashboard' : 'Dashboard')])

@php
use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="flex flex-col gap-8 md:gap-12">
    <!-- Read-Only Banner -->
    @if($readOnly)
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Viewing {{ $viewingUser->name }}'s Dashboard (Read-Only)
                </h3>
                <div class="mt-1 text-sm text-blue-700">
                    <p>You are viewing this dashboard as an authorized administrator. This is a read-only view.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Pre-Employment Banner & Link -->
    @if(isset($preEmployment) && $preEmployment->status === 'pre-employment' && !$readOnly)
    <div class="bg-teal-50 border-l-4 border-teal-400 p-4 rounded-lg mb-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-briefcase text-teal-400 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-teal-800">Pre-Employment Process Active</h3>
                <div class="mt-2 text-sm text-teal-700">
                    <p>You are currently in the pre-employment process. <a href="{{ route('pre-employment.portal') }}"
                            class="font-semibold underline hover:text-teal-900">Go to Pre-Employment Portal</a></p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Notifications/Alerts -->
    @if($hasPreEmployment && $checklistStats && $checklistStats['returned'] > 0 && !$readOnly)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Action Required</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>You have {{ $checklistStats['returned'] }} checklist {{ $checklistStats['returned'] === 1 ?
                        'item' : 'items' }} that need{{ $checklistStats['returned'] === 1 ? 's' : '' }} your attention.
                        <a href="{{ route('pre-employment.portal') }}"
                            class="font-semibold underline hover:text-yellow-900">Review now</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- User Welcome Card -->
    <div class="bg-teal-600 text-white rounded-xl shadow-lg p-6 flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl font-bold">
                {{ $viewingUser->initials }}
            </div>
            <div>
                <h2 class="text-2xl font-semibold">{{ $readOnly ? '' : 'Welcome, ' }}{{ $viewingUser->name }}{{
                    $readOnly ? '' : '!' }}</h2>
                <p class="text-sm opacity-80">{{ $viewingUser->email }}</p>
            </div>
        </div>
        @if(!$readOnly)
        <div class="mt-4 md:mt-0 flex flex-col gap-2">
            <a href="{{ route('settings.profile') }}"
                class="inline-block px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:bg-indigo-50 transition">Edit
                Profile</a>
            @if($viewingUser->google2fa_secret)
            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">MFA Enabled</span>
            @else
            <a href="{{ route('admin.mfa.setup.form') }}"
                class="inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg font-semibold shadow hover:bg-yellow-200 transition">Enable
                MFA</a>
            @endif
        </div>
        @endif
    </div>

    @if($roleStats)
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Hiring Overview</h3>
                @if($roleFacility)
                <p class="text-sm text-gray-500">Facility: {{ $roleFacility->name }}</p>
                @endif
            </div>
        </div>
        @php
        $applicantsUrl = $roleFacility
        ? route('admin.facility.hiring', ['facility' => $roleFacility->id])
        : route('admin.job-applications.index');
        $formsUrl = $roleFacility
        ? route('admin.facility.hiring', ['facility' => $roleFacility->id])
        : route('admin.job-applications.index');
        $openingsUrl = $roleFacility
        ? route('admin.facility.job_openings', ['facility' => $roleFacility->id])
        : route('admin.job-openings.index');
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <div class="bg-teal-50 border border-teal-100 rounded-lg p-4">
                <p class="text-xs font-semibold text-teal-700 uppercase">Applicants</p>
                <p class="text-2xl font-bold text-teal-900">{{ $roleStats['applicants_total'] }}</p>
                <p class="text-xs text-teal-700 mt-1">Today {{ $roleStats['applicants_today'] }} • 7d {{
                    $roleStats['applicants_week'] }}</p>
                <a href="{{ $applicantsUrl }}"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-teal-800 hover:text-teal-900">
                    View applicants <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <p class="text-xs font-semibold text-blue-700 uppercase">Submitted Forms</p>
                <p class="text-2xl font-bold text-blue-900">{{ $roleStats['submitted_forms'] }}</p>
                <a href="{{ $formsUrl }}"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-blue-800 hover:text-blue-900">
                    View submissions <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <p class="text-xs font-semibold text-amber-700 uppercase">Pending Reviews</p>
                <p class="text-2xl font-bold text-amber-900">{{ $roleStats['pending_reviews'] }}</p>
                <a href="{{ $formsUrl }}"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-amber-800 hover:text-amber-900">
                    Review pending <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="bg-orange-50 border border-orange-100 rounded-lg p-4">
                <p class="text-xs font-semibold text-orange-700 uppercase">Returned Forms</p>
                <p class="text-2xl font-bold text-orange-900">{{ $roleStats['returned_forms'] }}</p>
                <a href="{{ $formsUrl }}"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-orange-800 hover:text-orange-900">
                    View returns <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-lg p-4">
                <p class="text-xs font-semibold text-green-700 uppercase">Completed Forms</p>
                <p class="text-2xl font-bold text-green-900">{{ $roleStats['completed_forms'] }}</p>
                <a href="{{ $formsUrl }}"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-green-800 hover:text-green-900">
                    View completed <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                <p class="text-xs font-semibold text-slate-700 uppercase">Open Job Openings</p>
                <p class="text-2xl font-bold text-slate-900">{{ $roleStats['open_job_openings'] }}</p>
                <a href="{{ $openingsUrl }}"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-slate-800 hover:text-slate-900">
                    View openings <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    @include('partials.quick_stats')

    <!-- Pre-Employment Checklist Summary -->
    @if($hasPreEmployment && $jobApplication)
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Pre-Employment Checklist</h3>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                {{ $checklistStats['completed'] }} of {{ $checklistStats['total'] }} Complete
            </span>
        </div>

        @php
        $checklistItems = $viewingUser->employeeChecklists;
        $progress = $checklistStats['total'] > 0 ? ($checklistStats['completed'] / $checklistStats['total']) * 100 : 0;
        @endphp

        <div class="mb-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%">
                </div>
            </div>
        </div>

        @if($checklistItems->count() > 0)
        <ul class="space-y-2">
            @foreach($checklistItems as $item)
            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    @if($item->status === 'completed')
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    @elseif($item->status === 'submitted')
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                    @elseif($item->status === 'returned')
                    <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    @else
                    <i class="far fa-circle text-gray-400 text-xl"></i>
                    @endif
                    <span class="text-gray-700">{{ $item->item_label }}</span>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium
                    @if($item->status === 'completed') bg-green-100 text-green-700
                    @elseif($item->status === 'submitted') bg-blue-100 text-blue-700
                    @elseif($item->status === 'returned') bg-yellow-100 text-yellow-700
                    @else bg-gray-100 text-gray-700
                    @endif">
                    {{ ucfirst($item->status) }}
                </span>
            </li>
            @endforeach
        </ul>

        @if(!$readOnly)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <a href="{{ route('pre-employment.portal') }}"
                class="inline-block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg font-semibold shadow hover:bg-green-700 transition">
                <i class="fas fa-arrow-right mr-2"></i>View Full Checklist
            </a>
        </div>
        @endif
        @endif
    </div>
    @endif

    <!-- Recent Activity (placeholder) -->
    @include('partials.recent_activity')

    @if (session('status'))
    <div class="mb-4 text-green-600 text-center font-semibold">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
    <div class="mb-4 text-red-600 text-center font-semibold">
        @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif
</div>
@endsection