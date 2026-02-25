@extends('layouts.user_dashboard', ['title' => 'Pre-Employment Portal'])

@section('content')
<div class="flex flex-col gap-8" x-data="{ 
    activeItem: localStorage.getItem('pre_employment_active_item') || @if(session('success') || $errors->any()) 'application_packet' @else null @endif 
}" x-init="$watch('activeItem', value => { 
    if (value) localStorage.setItem('pre_employment_active_item', value); 
    else localStorage.removeItem('pre_employment_active_item'); 
});
@if(session('success') || $errors->any())
// Scroll to top to show messages
window.scrollTo({ top: 0, behavior: 'smooth' });
@endif
">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div id="success-message" class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-md">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
            <div>
                <h4 class="text-sm font-medium text-green-800">Success!</h4>
                <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div id="error-message" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-md">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-400 text-xl mr-3"></i>
            <div>
                <h4 class="text-sm font-medium text-red-800">Error!</h4>
                <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-xl shadow-lg p-8">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Welcome to Your Pre-Employment Portal</h1>
                <p class="text-teal-100 text-lg">Complete the steps below to finish your onboarding process</p>
            </div>
            <div class="bg-white/20 rounded-lg px-4 py-2">
                <div class="text-xs text-teal-100 uppercase font-semibold">Status</div>
                <div class="text-xl font-bold">In Progress</div>
            </div>
        </div>
    </div>

    <!-- Progress Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-gray-900">Completed</h3>
                <span class="text-2xl font-bold text-green-600">{{ $completedCount }}/{{ $checklistItems->count()
                    }}</span>
            </div>
            @php
            $completionPercent = $checklistItems->count() > 0
            ? round(($completedCount / $checklistItems->count()) * 100)
            : 0;
            @endphp
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $completionPercent }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-gray-900">Submitted</h3>
                <span class="text-2xl font-bold text-blue-600">{{ $inProgressCount }}</span>
            </div>
            <p class="text-sm text-gray-600">Items awaiting review</p>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-gray-300">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-gray-900">Draft / Returned</h3>
                <span class="text-2xl font-bold text-gray-600">{{ $pendingCount }}</span>
            </div>
            <p class="text-sm text-gray-600">Items waiting for completion</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Accordion Checklist -->
        <aside class="bg-white rounded-xl shadow-md p-6 lg:col-span-1">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Pre-Employment Checklist</h2>
            @php
            $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'returned' => 'Returned',
            'completed' => 'Completed',
            ];
            $statusClasses = [
            'draft' => 'bg-gray-200 text-gray-700',
            'submitted' => 'bg-blue-100 text-blue-700',
            'returned' => 'bg-amber-100 text-amber-800',
            'completed' => 'bg-green-100 text-green-700',
            ];
            @endphp
            <div class="space-y-2">
                @foreach ($checklistItems as $item)
                @php
                $status = $item->status ?? 'draft';
                $isEditable = in_array($status, ['draft', 'returned'], true);
                @endphp
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <!-- Accordion Header -->
                    <button
                        @click="activeItem = activeItem === '{{ $item->item_key }}' ? null : '{{ $item->item_key }}'"
                        class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition cursor-pointer"
                        :class="{ 'bg-teal-50 border-l-4 border-teal-600': activeItem === '{{ $item->item_key }}' }">
                        <div class="flex items-center gap-3">
                            @if($status === 'completed')
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                            @elseif($status === 'submitted')
                            <i class="fas fa-clock text-blue-600 text-lg"></i>
                            @elseif($status === 'returned')
                            <i class="fas fa-exclamation-circle text-amber-600 text-lg"></i>
                            @else
                            <i class="far fa-circle text-gray-400 text-lg"></i>
                            @endif
                            <span class="font-semibold text-gray-900 text-left">{{ $item->item_label }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-gray-200 text-gray-700' }}">
                                {{ $statusLabels[$status] ?? 'Pending' }}
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform"
                                :class="{ 'rotate-180': activeItem === '{{ $item->item_key }}' }"></i>
                        </div>
                    </button>
                </div>
                @endforeach
            </div>
        </aside>

        <!-- Right Column: Form Area -->
        <section class="lg:col-span-2 space-y-6">
            @foreach ($checklistItems as $item)
            @php
            $status = $item->status ?? 'draft';
            $isEditable = in_array($status, ['draft', 'returned'], true);
            @endphp
            <div x-show="activeItem === '{{ $item->item_key }}'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100" class="bg-white rounded-xl shadow-md p-8">

                <!-- Form Header -->
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $item->item_label }}</h3>
                            @if($item->submitted_at)
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-paper-plane mr-1"></i>
                                Submitted {{ $item->submitted_at->format('M j, Y g:i A') }}
                            </p>
                            @endif
                            @if($item->returned_at)
                            <p class="text-sm text-amber-700 mt-1">
                                <i class="fas fa-undo mr-1"></i>
                                Returned {{ $item->returned_at->format('M j, Y g:i A') }}
                            </p>
                            @endif
                            @if($item->completed_at)
                            <p class="text-sm text-green-700 mt-1">
                                <i class="fas fa-check-circle mr-1"></i>
                                Completed {{ $item->completed_at->format('M j, Y g:i A') }}
                            </p>
                            @endif
                        </div>
                        <span
                            class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusClasses[$status] ?? 'bg-gray-200 text-gray-700' }}">
                            {{ $statusLabels[$status] ?? 'Pending' }}
                        </span>
                    </div>
                </div>

                <!-- Status Messages -->
                @if ($status === 'submitted')
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-400 text-xl mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800">Submitted for Review</h4>
                            <p class="text-sm text-blue-700 mt-1">This item has been submitted and is awaiting review by
                                the hiring manager. You cannot edit until it is returned.</p>
                        </div>
                    </div>
                </div>
                @elseif ($status === 'returned')
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-amber-400 text-xl mr-3"></i>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-amber-800">Action Required</h4>
                            <p class="text-sm text-amber-700 mt-1">This item has been returned for edits. Please review
                                the feedback below, make necessary changes, and submit again.</p>

                            @php
                            $returnedActivity = null;
                            // Try to find the most recent returned activity for this user
                            if (auth()->check()) {
                            $query = \App\Models\HiringActivityLog::where('recipient_id', auth()->id())
                            ->where('activity_type', 'returned')
                            ->orderByDesc('created_at');

                            // If we have a pre-employment app, also filter by that
                            if ($preEmployment && $preEmployment->id) {
                            $query->where('pre_employment_application_id', $preEmployment->id);
                            }

                            $returnedActivity = $query->first();
                            }
                            @endphp

                            @if($returnedActivity)
                            <div class="mt-4 p-3 bg-white border-l-4 border-amber-400 rounded">
                                <p class="text-xs font-semibold text-amber-800 uppercase mb-2">
                                    <i class="fas fa-comment"></i> Hiring Manager's Feedback
                                </p>
                                @if($returnedActivity->form_type)
                                <p class="text-sm font-medium text-amber-700 mb-2">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    @php
                                    $formLabels = [
                                    'application_packet' => 'Application Packet',
                                    'personal' => 'Personal Information',
                                    'position' => 'Position Desired',
                                    'drivers_license' => "Driver's License",
                                    'work_authorization' => 'Work Authorization',
                                    'work_experience' => 'Work Experience',
                                    'education' => 'Education',
                                    'previous_addresses' => 'Previous Addresses',
                                    'other' => 'Other/Multiple Sections',
                                    ];
                                    @endphp
                                    Form Section: <strong>{{ $formLabels[$returnedActivity->form_type] ??
                                        ucfirst(str_replace('_', ' ', $returnedActivity->form_type)) }}</strong>
                                </p>
                                @endif

                                @if($returnedActivity->notes)
                                <p class="text-sm text-amber-900 whitespace-pre-wrap">{{ $returnedActivity->notes }}</p>
                                @else
                                <p class="text-sm text-amber-700 italic">No specific comments provided. Please contact
                                    the hiring manager for details.</p>
                                @endif

                                <p class="text-xs text-amber-700 mt-2">
                                    <i class="fas fa-clock mr-1"></i>Returned on {{
                                    $returnedActivity->created_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                            @else
                            <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                                <p class="text-sm text-yellow-800">
                                    <i class="fas fa-info-circle mr-2"></i>No feedback details found. Please contact the
                                    hiring manager for more information.
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @elseif ($status === 'completed')
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-green-800">Completed</h4>
                            <p class="text-sm text-green-700 mt-1">This item has been reviewed and approved by the
                                hiring manager.</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Form Content -->
                @if($item->item_key === 'application_packet')
                <!-- Employment Application Form -->
                @php
                $formStatus = $preEmployment?->status ?? $item->status ?? 'draft';
                @endphp
                @include('pre-employment.forms.application_packet', ['employee' => $employee, 'preEmployment' =>
                $preEmployment, 'jobApplication' => $jobApplication, 'status' => $formStatus,
                'positions' => $positions, 'selectedPositionId' => $selectedPositionId])
                @else
                <!-- Generic Notes Form -->
                <form method="POST" action="{{ route('pre-employment.checklist.update', $item) }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Notes / Documentation
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea name="notes" rows="8"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-700 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 transition"
                                placeholder="Please provide any relevant information, documentation details, or upload confirmations here..."
                                @if (!$isEditable) disabled @endif>{{ old('notes', $item->notes) }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Add detailed notes about this checklist item. Include any reference numbers, upload
                                confirmations, or additional information.
                            </p>
                        </div>

                        @if ($isEditable)
                        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                            <button type="submit" name="action" value="save"
                                class="px-6 py-3 rounded-lg text-sm font-semibold border-2 border-teal-600 text-teal-700 hover:bg-teal-50 transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-save"></i>
                                Save Draft
                            </button>
                            <button type="submit" name="action" value="submit"
                                class="px-6 py-3 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700 transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-paper-plane"></i>
                                Submit for Review
                            </button>
                        </div>
                        @endif
                    </div>
                </form>
                @endif
            </div>
            @endforeach

            <!-- Welcome/Instructions Card (shown when no item is selected) -->
            <div x-show="activeItem === null" x-transition class="bg-white rounded-xl shadow-md p-8" x-cloak>
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-teal-100 rounded-full mb-4">
                        <i class="fas fa-clipboard-list text-teal-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Get Started with Your Checklist</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Click on any checklist item from the left panel to view details, fill out forms, and track your
                        progress through the pre-employment process.
                    </p>
                    <div class="bg-teal-50 rounded-lg p-6 border border-teal-200 text-left max-w-lg mx-auto">
                        <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-lightbulb text-teal-600"></i>
                            Tips for Success
                        </h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-teal-600 mt-1"></i>
                                <span>Save your progress frequently using the "Save Draft" button</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-teal-600 mt-1"></i>
                                <span>Submit items one at a time for review by the hiring manager</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-teal-600 mt-1"></i>
                                <span>Check your email for notifications about returned items</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-teal-600 mt-1"></i>
                                <span>Contact HR if you need help with any step</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-teal-50 rounded-xl p-6 border-l-4 border-teal-600">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Need Help?</h3>
                <p class="text-gray-700 mb-4">
                    If you have any questions or need assistance with your pre-employment process, please contact our HR
                    department.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="mailto:hr@biopacific.com"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white text-teal-700 rounded-lg font-semibold border border-teal-600 hover:bg-teal-50 transition cursor-pointer">
                        <i class="fas fa-envelope"></i>
                        Email HR
                    </a>
                    <a href="tel:+1234567890"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white text-teal-700 rounded-lg font-semibold border border-teal-600 hover:bg-teal-50 transition cursor-pointer">
                        <i class="fas fa-phone"></i>
                        Call HR
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection