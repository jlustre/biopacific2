<div id="job-application-form" x-data="{ 
        scrollToTop() {
            document.getElementById('job-application-form').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }"
    style="--primary-color: {{ $primary }}; --secondary-color: {{ $secondary }}; --accent-color: {{ $accent }}; --neutral-dark: {{ $neutral_dark }}; --neutral-light: {{ $neutral_light }};"
    @scroll-to-top.window="scrollToTop()">
    <style>
        .custom-focus:focus {
            --tw-ring-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .custom-checkbox:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
    <!-- Success Message -->
    @if($successMessage)
    <x-success-message id="success-message" wire:key="success-{{ now() }}">
        {{ $successMessage }}
    </x-success-message>
    @elseif($jobOpening)
    <!-- Job-specific application form -->
    <form wire:submit.prevent="submit" class="space-y-6">
        <!-- HIPAA Compliance Notice -->
        <div class="mb-4 p-4 rounded-lg bg-yellow-50 border-l-4 border-yellow-400">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold text-yellow-700">HIPAA Compliance Notice</span>
            </div>
            <p class="text-yellow-700 text-sm">
                Please do not include any sensitive health information (such as medical records, diagnoses, or treatment
                details) in your application or attachments. All information submitted will be handled in accordance
                with HIPAA privacy and security regulations.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="first_name" wire:model="first_name"
                    class="block w-full rounded-lg  border border-teal-300 shadow-sm custom-focus px-2 py-1">
                @error('first_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="last_name" wire:model="last_name"
                    class="block w-full rounded-lg  border border-teal-300 shadow-sm custom-focus px-2 py-1">
                @error('last_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span
                    class="text-red-500">*</span></label>
            <input type="email" id="email" wire:model="email"
                class="block w-full rounded-lg border-gray-300 shadow-sm custom-focus px-2 py-1">
            @error('email')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone <span
                    class="text-red-500">*</span></label>
            <input type="tel" id="phone" wire:model="phone"
                class="block w-full rounded-lg  border border-teal-300 shadow-sm custom-focus px-2 py-1">
            @error('phone')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="resume" class="block text-sm font-medium text-gray-700 mb-2">Resume <span
                    class="text-red-500">*</span></label>
            <input type="file" id="resume" wire:model="resume" accept=".pdf,.doc,.docx"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1">
            @error('resume')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">Cover Letter</label>
            <textarea id="cover_letter" wire:model="cover_letter" rows="4"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1"
                placeholder="Tell us about yourself and your interest in working here..."></textarea>
            @error('cover_letter')
            <p class="text-red-500 text-sm -mt-2">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-start space-x-3">
            <input type="checkbox" id="consent" wire:model="consent"
                class="mt-1 h-4 w-4 text-blue-600 border border-teal-300 rounded focus:ring-blue-500">
            <label for="consent" class="text-sm text-gray-700">
                I consent to the processing of my personal data for recruitment purposes and understand that my
                information will be used to evaluate my application for this title.
                <span class="text-red-500">*</span>
            </label>
        </div>
        @error('consent')
        <p class="text-red-500 text-sm -mt-2">{{ $message }}</p>
        @enderror
        <div class="flex items-start space-x-3 mt-1">
            <input type="checkbox" id="hipaa_consent" wire:model="hipaa_consent"
                class="mt-1 h-4 w-4 text-yellow-600 border border-teal-300 rounded focus:ring-yellow-500">
            <label for="hipaa_consent" class="text-sm text-yellow-700">
                I acknowledge and consent that my application will be handled in accordance with HIPAA privacy and
                security regulations, and I will not include any protected health information in my submission.
                <span class="text-red-500">*</span>
            </label>
        </div>
        @error('hipaa_consent')
        <p class="text-red-500 text-sm -mt-2">{{ $message }}</p>
        @enderror
        <div class="flex justify-end pt-6 border-t border-gray-200">
            <x-primary-button type="submit" size="lg" :loading="$isSubmitting" loading-text="Submitting..."
                icon="fas fa-paper-plane" :primary="$primary" :secondary="$secondary" :accent="$accent"
                :neutral_dark="$neutral_dark" :neutral_light="$neutral_light" wire:loading.attr="disabled">
                Submit Application
            </x-primary-button>
        </div>
    </form>
    @elseif($facility)
    <!-- General Application Form (no job selected) -->
    <form wire:submit.prevent="submitGeneral" class="space-y-6">
        @if($errorMessage)
        <div class="mb-4 p-4 rounded-lg bg-red-50 border-l-4 border-red-400">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold text-red-700">Error</span>
            </div>
            <p class="text-red-700 text-sm">
                {{ $errorMessage }}
            </p>
        </div>
        @endif
        <!-- HIPAA Compliance Notice -->
        <div class="mb-4 p-4 rounded-lg bg-yellow-50 border-l-4 border-yellow-400">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold text-yellow-700">HIPAA Compliance Notice</span>
            </div>
            <p class="text-yellow-700 text-sm">
                Please do not include any sensitive health information (such as medical records, diagnoses, or treatment
                details) in your application or attachments. All information submitted will be handled in accordance
                with HIPAA privacy and security regulations.
            </p>
        </div>
        <div class="mb-4">
            <label for="desired_position" class="block text-sm font-medium text-gray-700 mb-2">
                Desired Position or Title <span class="text-red-500">*</span>
            </label>
            <input type="text" id="desired_position" wire:model="desired_position"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1"
                placeholder="e.g. Nurse, Administrator, Therapist">
            @error('desired_position')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                Department <span class="text-red-500">*</span>
            </label>
            <select id="department" wire:model="department"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1">
                <option value="">Select Department</option>
                <option value="Nursing">Nursing</option>
                <option value="Administration">Administration</option>
                <option value="Therapy">Therapy</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Housekeeping">Housekeeping</option>
                <option value="Food Services">Food Services</option>
                <option value="Other">Other</option>
            </select>
            @error('department')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-2">
                Employment Type <span class="text-red-500">*</span>
            </label>
            <select id="employment_type" wire:model="employment_type"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1">
                <option value="">Select Employment Type</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Per Diem">Per Diem</option>
                <option value="Temporary">Temporary</option>
                <option value="Any">Any</option>
            </select>
            @error('employment_type')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="first_name" wire:model="first_name"
                    class="block w-full rounded-lg  border border-teal-300 shadow-sm custom-focus px-2 py-1">
                @error('first_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="last_name" wire:model="last_name"
                    class="block w-full rounded-lg  border border-teal-300 shadow-sm custom-focus px-2 py-1">
                @error('last_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span
                    class="text-red-500">*</span></label>
            <input type="email" id="email" wire:model="email"
                class="block w-full rounded-lg border-gray-300 shadow-sm custom-focus px-2 py-1">
            @error('email')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone <span
                    class="text-red-500">*</span></label>
            <input type="tel" id="phone" wire:model="phone"
                class="block w-full rounded-lg  border border-teal-300 shadow-sm custom-focus px-2 py-1">
            @error('phone')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="resume" class="block text-sm font-medium text-gray-700 mb-2">Resume <span
                    class="text-red-500">*</span></label>
            <input type="file" id="resume" wire:model="resume" accept=".pdf,.doc,.docx"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1">
            @error('resume')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">Cover Letter</label>
            <textarea id="cover_letter" wire:model="cover_letter" rows="4"
                class="block w-full rounded-lg border border-teal-300 shadow-sm custom-focus px-2 py-1"
                placeholder="Tell us about yourself and your interest in working here..."></textarea>
            @error('cover_letter')
            <p class="text-red-500 text-sm -mt-2">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-start space-x-3">
            <input type="checkbox" id="consent" wire:model="consent"
                class="mt-1 h-4 w-4 text-blue-600 border border-teal-300 rounded focus:ring-blue-500">
            <label for="consent" class="text-sm text-gray-700">
                I consent to the processing of my personal data for recruitment purposes and understand that my
                information will be used to evaluate my application for this position.
                <span class="text-red-500">*</span>
            </label>
        </div>
        @error('consent')
        <p class="text-red-500 text-sm -mt-2">{{ $message }}</p>
        @enderror
        <div class="flex items-start space-x-3 mt-1">
            <input type="checkbox" id="hipaa_consent" wire:model="hipaa_consent"
                class="mt-1 h-4 w-4 text-yellow-600 border border-teal-300 rounded focus:ring-yellow-500">
            <label for="hipaa_consent" class="text-sm text-yellow-700">
                I acknowledge and consent that my application will be handled in accordance with HIPAA privacy and
                security regulations, and I will not include any protected health information in my submission.
                <span class="text-red-500">*</span>
            </label>
        </div>
        @error('hipaa_consent')
        <p class="text-red-500 text-sm -mt-2">{{ $message }}</p>
        @enderror
        <div class="flex justify-end pt-6 border-t border-gray-200">
            <x-primary-button type="submit" size="lg" :loading="$isSubmitting" loading-text="Submitting..."
                icon="fas fa-paper-plane" :primary="$primary" :secondary="$secondary" :accent="$accent"
                :neutral_dark="$neutral_dark" :neutral_light="$neutral_light" wire:loading.attr="disabled">
                Submit Application
            </x-primary-button>
        </div>
    </form>
    @else
    <!-- Select a Position message -->
    <div class="py-16 text-center">
        <h2 class="text-2xl font-bold mb-2">Select a Position</h2>
        <p class="text-gray-500">Please select a job opening to apply for.</p>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToTop', () => {
            document.getElementById('success-message')?.scrollIntoView({ 
                behavior: 'smooth',
                block: 'center' 
            });
        });
    });
    </script>