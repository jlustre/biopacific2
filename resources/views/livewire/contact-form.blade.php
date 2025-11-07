{{-- Livewire Contact Form Component --}}
<div id="contact-form" class="rounded-3xl border bg-white p-6 sm:p-8 shadow-xl h-full">
    <div class="flex items-center mb-6">
        <div class="mr-4 inline-flex h-10 w-10 items-center justify-center rounded-full"
            style="background: {{ $primary }}1A; color: {{ $primary }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.4-4 8-9 8-1.5 0-3-.3-4.3-.9L3 20l1.4-3.7A8.9 8.9 0 013 12c0-4.4 4-8 9-8s9 3.6 9 8z" />
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-semibold text-slate-900">Send us a Message</h3>
            <p class="text-sm text-slate-500">We'll get back to you promptly.</p>
        </div>
    </div>

    <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800 mb-4">
        ⚠ Please avoid sharing personal medical details (PHI) in this form. We'll discuss specifics
        privately.
    </div>

    {{-- Success Message --}}
    @if($successMessage)
    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"></path>
            </svg>
            {{ $successMessage }}
        </div>
    </div>
    @endif

    {{-- Error Message Summary --}}
    @if($errorMessage || $errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm">
                <strong>Please fix the following issues:</strong>
                <ul class="mt-2 space-y-1">
                    @if($errors->any())
                    @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                    @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
    <form class="space-y-6" wire:submit.prevent="submit" novalidate>
        <div>
            <label for="contact_name" class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
            <input id="contact_name" wire:model="full_name" type="text" required autocomplete="name"
                class="w-full rounded-lg border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition @error('full_name') border-red-300 @enderror"
                placeholder="Enter your full name">
            @error('full_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact_phone" class="block text-sm font-medium text-slate-700 mb-2">Phone *</label>
            <input id="contact_phone" wire:model="phone" type="tel" inputmode="tel" autocomplete="tel" required
                class="w-full rounded-lg border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition @error('phone') border-red-300 @enderror"
                placeholder="(555) 123-4567">
            @error('phone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact_email" class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
            <input id="contact_email" wire:model="email" type="email" required autocomplete="email"
                class="w-full rounded-lg border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition @error('email') border-red-300 @enderror"
                placeholder="your@email.com">
            @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="contact_message" class="block text-sm font-medium text-slate-700 mb-2">Message *</label>
            <textarea id="contact_message" wire:model="message" rows="4" required
                class="w-full rounded-lg border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition @error('message') border-red-300 @enderror"
                placeholder="How can we help you today?"></textarea>
            @error('message')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-start gap-4">
            <label class="inline-flex items-start gap-2 text-sm text-slate-600">
                <input wire:model="consent" type="checkbox" required
                    class="mt-1 h-4 w-4 rounded text-primary focus:ring-primary/30 @error('consent') border-red-500 ring-2 ring-red-200 @enderror"
                    style="border: 1px solid {{ $primary ?? '#0EA5E9' }};">
                I consent to be contacted about my inquiry. *
            </label>
            {{-- Honeypot field --}}
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" wire:model="website">
        </div>
        @error('consent')
        <div class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">{{ $message }}</div>
        @enderror

        <div class="flex items-start mb-6">
            <input id="no-phi" wire:model="no_phi" type="checkbox" required
                class="mt-1 h-4 w-4 rounded text-primary focus:ring-primary/30 @error('no_phi') border-red-500 ring-2 ring-red-200 @enderror"
                style="border: 1px solid {{ $primary ?? '#0EA5E9' }};">
            <label for="no-phi" class="ml-2 block text-sm text-gray-700">
                I confirm that I will not include any Protected Health Information (PHI) in this form. *
            </label>
        </div>
        @error('no_phi')
        <div class="mb-4 -mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">{{ $message }}</div>
        @enderror

        <p class="text-xs mb-6 text-slate-500">See our <a
                href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}"
                class="underline hover:text-primary" target="_blank" rel="noopener noreferrer">Notice of Privacy
                Practices</a>.</p>


        <div class="flex flex-col sm:flex-row justify-end gap-3">
            <button type="reset" wire:click="$refresh"
                class="px-6 py-2.5 rounded-lg text-slate-700 hover:bg-slate-50 transition"
                style="border: 1px solid {{ $secondary ?? '#155E75' }}; color: {{ $neutral_dark ?? '#1e293b' }};">
                Clear Form
            </button>

            <x-primary-button type="submit" :loading="$isSubmitting" loading-text="Sending..." icon="fas fa-paper-plane"
                :primary="$primary" :secondary="$secondary" :accent="$accent" :neutral_dark="$neutral_dark"
                :neutral_light="$neutral_light" wire:loading.attr="disabled" wire:target="submit">
                Send Message
            </x-primary-button>
        </div>

        {{-- Redundant mobile error display removed --}}
    </form>
</div>

<script>
    document.addEventListener('livewire:init', () => {
    Livewire.on('scroll-to-form', () => {
        const form = document.getElementById('contact-form');
        if (form) {
            form.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    });
});
</script>