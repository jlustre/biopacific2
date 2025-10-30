<div class="mt-4 border rounded-xl border-amber-300 bg-white/80 p-6">
    <h2 class="text-xl font-semibold text-slate-800 mb-4">Contact Us</h2>
    <p class="text-sm text-slate-600 mb-4">
        We’re here to assist you with any questions or concerns you may have. Feel free to reach out for inquiries about
        our services, facilities, or general information. If you’re looking to schedule a tour, please use our <a
            href="#book" class="text-primary underline">Book a Tour</a> form.
    </p>
    @if (session()->has('success'))
    <div class="p-4 mb-4 text-green-800 bg-green-200 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="p-4 mb-4 text-red-800 bg-red-200 rounded-lg">
        {{ session('error') }}
    </div>
    @endif
    <form class="space-y-6" wire:submit.prevent="submit" novalidate>
        <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800 mb-6">
            ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
            privately.
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label for="full_name" class="block text-sm font-medium text-slate-700 mb-1">Full Name
                    *</label>
                <input id="full_name" type="text" required autocomplete="name" wire:model="full_name"
                    class="w-full rounded-md border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                    placeholder="Enter your full name">
                @error('full_name') <span class="text-red-600 text-sm" wire:key="error-full_name">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input id="phone" type="tel" inputmode="tel" autocomplete="tel" wire:model="phone"
                    class="w-full rounded-md border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                    placeholder="(555) 123-4567">
                @error('phone') <span class="text-red-600 text-sm" wire:key="error-phone">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address
                    *</label>
                <input id="email" type="email" required autocomplete="email" wire:model="email"
                    class="w-full rounded-md border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                    placeholder="your@email.com">
                @error('email') <span class="text-red-600 text-sm" wire:key="error-email">{{ $message }}</span>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Message
                    *</label>
                <textarea id="message" rows="5" required wire:model="message"
                    class="w-full rounded-md border border-teal-400 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                    placeholder="How can we help you today?"></textarea>
                @error('message') <span class="text-red-600 text-sm" wire:key="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4 mt-2">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" class="rounded border-teal-400 text-primary focus:ring-primary/30"
                    wire:model="consent">
                I consent to be contacted about my inquiry.
            </label>
            @error('consent') <span class="text-red-600 text-sm" wire:key="error-consent">{{ $message }}</span>
            @enderror
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" wire:model="website">
            <!-- honeypot -->
        </div>
        <div class="flex flex-col gap-2 mt-2">
            <div class="flex items-center">
                <input id="no_phi" wire:model="no_phi" type="checkbox" required
                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="no_phi" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">I confirm that I
                    will not include any Protected Health Information (PHI) in this form.</label><br />
                @error('no_phi') <span class="text-red-600 text-sm" wire:key="error-no_phi">{{ $message }}</span>
                @enderror
            </div>
            <p class="text-xs">See our <a href="{{ $facility['slug'] ?? '#' }}/notice-of-privacy-practices"
                    class="underline text-primary" target="_blank" rel="noopener noreferrer">Notice of Privacy
                    Practices</a>.</p>
        </div>
        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-4">
            <button type="reset"
                class="px-6 py-2.5 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 transition">Clear
                Form</button>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-white transition shadow-sm hover:shadow"
                style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}">Send Message</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('scrollToTop', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>