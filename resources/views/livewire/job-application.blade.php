<div>
    <!-- Success Message -->
    @if($successMessage)
    <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300" id="success-message" wire:key="success-{{ now() }}">
        <i class="fas fa-check-circle mr-2"></i>{{ $successMessage }}
    </div>
    @endif

    <!-- Error Message -->
    @if($errorMessage)
    <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800 border border-red-300">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errorMessage }}
    </div>
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
                    class="w-full rounded-lg border @error('first_name') border-red-300 bg-red-50 @else border-gray-300 @enderror px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                <span wire:loading.remove wire:target="submit">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Application
                </span>
                <span wire:loading wire:target="submit">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Submitting...
                </span>
            </button>
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