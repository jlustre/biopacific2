<div>
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

    <form wire:submit.prevent="submit" class="space-y-6" novalidate>
        {{-- Step 1: Contact --}}
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="full_name" class="text-base font-medium text-slate-700">Your name *</label>
                <input id="full_name" type="text" wire:model="full_name" required placeholder="Jane Doe"
                    class="mt-1 block w-full rounded-sm px-2 py-1 border border-teal-500 focus:border-teal-600 focus:ring-0" />
                @error('full_name') <span class="text-red-600 text-sm" wire:key="error-full_name">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="relationship" class="text-base font-medium text-slate-700">Relationship</label>
                <select id="relationship" wire:model="relationship"
                    class="mt-1 block w-full rounded-sm px-2 py-1 border border-teal-500 focus:border-teal-600 focus:ring-0">
                    <option value="">Select…</option>
                    <option>Self</option>
                    <option>Spouse</option>
                    <option>Parent</option>
                    <option>Adult child</option>
                    <option>Relative</option>
                    <option>Friend</option>
                    <option>Care manager</option>
                    <option>Others</option>
                </select>
                @error('relationship') <span class="text-red-600 text-sm" wire:key="error-relationship">{{ $message
                    }}</span> @enderror
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="phone" class="text-base font-medium text-slate-700">Your Phone *</label>
                <input id="phone" type="tel" wire:model="phone" required placeholder="(555) 555-1234"
                    class="mt-1 block w-full rounded-sm px-2 py-1 border border-teal-500 focus:border-teal-600 focus:ring-0" />
                @error('phone') <span class="text-red-600 text-sm" wire:key="error-phone">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="email" class="text-base font-medium text-slate-700">Your Email *</label>
                <input id="email" type="email" wire:model="email" required placeholder="you@example.com"
                    class="mt-1 block w-full rounded-sm px-2 py-1 border border-teal-500 focus:border-teal-600 focus:ring-0" />
                @error('email') <span class="text-red-600 text-sm" wire:key="error-email">{{ $message }}</span>
                @enderror
            </div>
        </div>
        {{-- Step 2: Date & time --}}
        <div>
            <label for="preferred_date" class="text-base font-medium text-slate-700">Preferred date
                *</label>
            <input id="preferred_date" type="date" wire:model="preferred_date" required
                class="mt-1 block w-full rounded-sm px-2 py-1 border border-teal-500 focus:border-teal-600 focus:ring-0" />
            @error('preferred_date') <span class="text-red-600 text-sm" wire:key="error-preferred_date">{{ $message
                }}</span> @enderror
            <div class="mt-4">
                <div class="text-base font-medium text-slate-700">Pick a time</div>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach(['9:00 AM','10:30 AM','1:00 PM','2:30 PM','4:00 PM'] as $slot)
                    <label class="group relative">
                        <input type="radio" wire:model.lazy="preferred_time" value="{{ $slot }}" class="peer sr-only"
                            required wire:input="$set('specific_time', '')">
                        <span
                            class="block rounded-sm border border-teal-600 bg-white px-2 py-1 text-base text-slate-700 peer-checked:bg-sky-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:border-sky-500 transition select-none cursor-pointer"
                            style="background:#fff; color:inherit;">
                            {{ $slot }}
                        </span>
                        <span
                            class="pointer-events-none absolute inset-0 rounded-sm px-2 -pt-1 ring-2 ring-transparent peer-checked:ring-teal-700"></span>
                    </label>
                    @endforeach

                    <select id="specific_time" wire:model.lazy="specific_time"
                        class="-mt-1 block w-full rounded-sm px-2 border border-teal-600 focus:border-teal-800 focus:ring-0"
                        wire:input="$set('preferred_time', '')">
                        <option value="">Select a time...</option>
                        @foreach(range(strtotime('08:00 AM'), strtotime('07:00 PM'), 30 * 60) as $time)
                        <option value="{{ date('g:i A', $time) }}">{{ date('g:i A', $time) }}</option>
                        @endforeach
                    </select>
                </div>
                @error('preferred_time') <span class="text-red-600 text-sm" wire:key="error-preferred_time">{{ $message
                    }}</span> @enderror

            </div>
        </div>

        <div>
            <fieldset>
                <legend class="text-base font-medium text-slate-700">Areas of interest</legend>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($services as $service)
                    <label
                        class="inline-flex items-center gap-2 rounded-sm border bg-slate-100 border-teal-500 px-2 py-1 text-base">
                        <input type="checkbox" wire:model="interests" value="{{ $service->name }}"
                            class="rounded-md border-teal-500 focus:border-teal-600 text-sky-600 focus:ring-sky-500">
                        <span>{{ $service->name }}</span>
                    </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="mt-4">
                <label for="message" class="text-base font-medium text-slate-700">Notes
                    (optional)</label>
                <textarea id="message" wire:model="message" rows="3"
                    class="mt-1 block w-full rounded-sm px-2 py-1 border border-teal-500 focus:border-teal-600 focus:ring-0"
                    placeholder="Accessibility needs, questions, preferences…"></textarea>
                @error('message') <span class="text-red-600 text-sm" wire:ignore.self>{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="space-y-2 text-sm text-slate-600">
            <label class="inline-flex items-start gap-2">
                <input type="checkbox" wire:model="consent" required
                    class="mt-0.5 rounded border-slate-500 focus:border-slate-600 text-sky-600 focus:ring-sky-500">
                <span class="-mt-1">I agree to be contacted about this tour request. <strong>Please do not include
                        sensitive
                        medical information.</strong></span>
            </label>
            @error('consent') <span class="text-red-600 text-sm" wire:ignore.self>{{ $message }}</span> @enderror
            <p>See our <a href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}"
                    class="underline text-primary" target="_blank" rel="noopener noreferrer">Notice of
                    Privacy Practices</a>.</p>
        </div>
        <div class="mt-7 flex flex-col sm:flex-row gap-4 justify-end">
            <button type="submit"
                class="cursor-pointer inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-7 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                style="background: {{ $primary }}">Request Tour</button>
            <a href="tel:{{ $facility['phone'] ?? '' }}"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-7 py-3 font-semibold ring-2 transition bg-white hover:bg-slate-100"
                style="border-color: {{ $secondary }}; color: {{ $secondary }};">Call Us: <span
                    class="inline-flex items-center px-3 py-2 text-base text-slate-600">{{
                    isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3',
                    preg_replace('/\D/', '', $facility['phone'])) : '' }}</span></a>
        </div>
    </form>

</div>