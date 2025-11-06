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
    @endif

    <!-- Error Message -->
    @if($errorMessage)
    <x-error-message dismissible="true">
        {{ $errorMessage }}
    </x-error-message>
    @endif

    @if($jobOpening && $facility)
    <!-- Form Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">Apply for Position</h3>
            <p class="text-gray-600 mt-1">{{ $jobOpening->title }} at {{ $facility->name }}</p>
        </div>
    </div>
    @else
    <div class="text-center py-8">
        <h3 class="text-xl font-bold text-gray-900 mb-2">Select a Position</h3>
        <p class="text-gray-600">Please select a job opening to apply for.</p>
    </div>
    @endif

    @if($jobOpening && $facility)

    <!-- Application Form -->
    <form wire:submit.prevent="submit" class="space-y-6">
        <!-- Personal Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="first_name" wire:model="first_name"
                    class="w-full rounded-lg border @error('first_name') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:border-transparent transition-all custom-focus"
                    placeholder="Enter your first name">
                @error('first_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="last_name" wire:model="last_name"
                    class="w-full rounded-lg border @error('last_name') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter your last name">
                @error('last_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Contact Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" wire:model="email"
                    class="w-full rounded-lg border @error('email') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="your.email@example.com">
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="phone" wire:model="phone"
                    class="w-full rounded-lg border @error('phone') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="(555) 123-4567">
                @error('phone')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Resume Upload -->
        <div>
            <label for="resume" class="block text-sm font-medium text-gray-700 mb-2">
                Resume <span class="text-red-500">*</span>
                <span class="text-gray-500 font-normal">(PDF, DOC, DOCX - Max 10MB)</span>
            </label>
            <div class="relative">
                <input type="file" id="resume" wire:model="resume" accept=".pdf,.doc,.docx"
                    class="w-full rounded-lg border @error('resume') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <div wire:loading wire:target="resume" class="absolute right-3 top-2.5">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                </div>
            </div>
            @error('resume')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            @if($resume)
            <p class="text-green-600 text-sm mt-1">
                <i class="fas fa-check mr-1"></i>File selected: {{ $resume->getClientOriginalName() }}
            </p>
            @endif
        </div>

        <!-- Cover Letter -->
        <div>
            <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">
                Cover Letter <span class="text-gray-500">(Optional)</span>
            </label>
            <textarea id="cover_letter" wire:model="cover_letter" rows="5"
                class="w-full rounded-lg border @error('cover_letter') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Tell us why you're interested in this position and what makes you a great fit..."></textarea>
            @error('cover_letter')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Consent -->
        <div class="flex items-start space-x-3">
            <input type="checkbox" id="consent" wire:model="consent"
                class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <label for="consent" class="text-sm text-gray-700">
                I consent to the processing of my personal data for recruitment purposes and understand that my
                information will be used to evaluate my application for this position.
                <span class="text-red-500">*</span>
            </label>
        </div>
        @error('consent')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

        <!-- Submit Button -->
        <div class="flex justify-end pt-6 border-t border-gray-200">
            <x-primary-button type="submit" size="lg" :loading="$isSubmitting" loading-text="Submitting..."
                icon="fas fa-paper-plane" :primary="$primary" :secondary="$secondary" :accent="$accent"
                :neutral_dark="$neutral_dark" :neutral_light="$neutral_light" wire:loading.attr="disabled">
                Submit Application
            </x-primary-button>
        </div>
    </form>
    @endif
</div>

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