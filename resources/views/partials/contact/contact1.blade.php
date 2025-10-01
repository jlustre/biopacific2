{{-- CONTACT — Side-by-side on md+, filled Contact Info --}}
<section id="contact" class="relative overflow-hidden py-20 md:py-28">
    {{-- Background / brand blobs --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
        @php
        if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
        $scheme = \DB::table('color_schemes')->where('id', $facility['color_scheme_id'])->first();
        $primary = $scheme ? ($scheme->primary_color ?? '#0EA5E9') : '#0EA5E9';
        $secondary = $scheme ? ($scheme->secondary_color ?? '#155E75') : '#155E75';
        $accent = $scheme ? ($scheme->accent_color ?? '#F59E0B') : '#F59E0B';
        } else {
        $primary = '#0EA5E9';
        $secondary = '#155E75';
        $accent = '#F59E0B';
        }
        @endphp
        <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-25"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-96 w-96 rounded-full blur-3xl opacity-20"
            style="background: {{ $accent }}"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl mb-8">
            <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800">
                ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics privately.
            </div>
        </div>
        @include('partials.section_header', [
        'section_header' => 'Get in Touch',
        'section_sub_header' => "Have questions? We're here to help you every step of the
        way."
        ])

        @php
        // Phone formatting
        $p_raw = preg_replace('/\D/','', $facility['phone'] ?? '');
        $p_fmt = $p_raw ? sprintf('(%s) %s-%s', substr($p_raw,0,3), substr($p_raw,3,3), substr($p_raw,6,4)) : 'N/A';

        // Maps link fallback
        $mapsEmbed = $facility['location_map'] ?? null;
        $mapsHref = (isset($mapsEmbed) && \Illuminate\Support\Str::startsWith($mapsEmbed, ['http://','https://']))
        ? $mapsEmbed
        : 'https://www.google.com/maps?q=' . urlencode(trim(($facility['address']??'').' '.($facility['city']??'').'
        '.($facility['state']??'').' '.($facility['zip']??'')));
        @endphp

        {{-- 1 column on small, 3 columns on md+ --}}
        <div class="mt-10 grid gap-8 md:grid-cols-3 items-start">

            {{-- Contact Information (FILLED) --}}
            <aside class="rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-xl p-6 sm:p-8 h-full">
                <h3 class="text-2xl font-extrabold text-slate-900">Contact Information</h3>
                <p class="mt-1 text-sm text-slate-600">We usually respond within 1 business day.</p>

                <dl class="mt-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl"
                            style="background: {{ $primary }}1A; color: {{ $primary }};">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h2.6a1 1 0 01.95.69l1.3 3.9a1 1 0 01-.47 1.2l-1.8.9a10.5 10.5 0 005.3 5.3l.9-1.8a1 1 0 011.2-.47l3.9 1.3a1 1 0 01.69.95V19a2 2 0 01-2 2h-1C9.16 21 3 14.84 3 7V5z" />
                            </svg>
                        </span>
                        <div>
                            <dt class="text-sm font-semibold text-slate-700">Phone</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                @if($p_raw)
                                <a href="tel:{{ $p_raw }}" class="hover:underline">{{ $p_fmt }}</a>
                                @else N/A @endif
                            </dd>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl"
                            style="background: {{ $secondary }}1A; color: {{ $secondary }};">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.9 4.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5A2 2 0 003 7v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <dt class="text-sm font-semibold text-slate-700">Email</dt>
                            <dd class="text-lg font-medium">
                                @if(!empty($facility['email']))
                                <a href="mailto:{{ $facility['email'] }}"
                                    class="text-slate-900 hover:text-slate-700 hover:underline">
                                    {{ $facility['email'] }}
                                </a>
                                @else N/A @endif
                            </dd>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl"
                            style="background: {{ $accent }}1A; color: {{ $accent }};">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.7 17.7L12 23l-5.7-5.3A8 8 0 1117.7 17.7z" />
                                <circle cx="12" cy="11" r="3" />
                            </svg>
                        </span>
                        <div>
                            <dt class="text-sm font-semibold text-slate-700">Address</dt>
                            <dd class="text-slate-900">
                                {{ $facility['address'] ?? '' }}{{ !empty($facility['city']) ? ', ' . $facility['city']
                                : '' }}{{ !empty($facility['state']) ? ', ' . $facility['state'] : '' }} {{
                                $facility['zip'] ?? '' }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <dt class="text-sm font-semibold text-slate-700">Visiting Hours</dt>
                            <dd class="text-slate-900">{{ $facility['hours'] ?? '10:30 AM – 8:30 PM' }}</dd>
                        </div>
                    </div>
                </dl>

                {{-- Optional socials --}}
                @if(!empty($facility['social']))
                <div class="mt-6 flex items-center gap-3">
                    @foreach($facility['social'] as $label => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border hover:bg-slate-50"
                        aria-label="{{ ucfirst($label) }}">
                        <span class="text-xs font-semibold">{{ \Illuminate\Support\Str::substr($label,0,2) }}</span>
                    </a>
                    @endforeach
                </div>
                @endif
            </aside>

            {{-- Contact Form (stacked fields; full width each) --}}
            <div class="rounded-3xl border bg-white p-6 sm:p-8 shadow-xl h-full">
                <form class="space-y-6">
                    <div class="flex items-center">
                        <div class="mr-4 inline-flex h-10 w-10 items-center justify-center rounded-full"
                            style="background: {{ ($facility['primary_color'] ?? '#0EA5E9') }}1A; color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.4-4 8-9 8-1.5 0-3-.3-4.3-.9L3 20l1.4-3.7A8.9 8.9 0 013 12c0-4.4 4-8 9-8s9 3.6 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Send us a Message</h3>
                            <p class="text-sm text-slate-500">We’ll get back to you promptly.</p>
                        </div>
                    </div>

                    <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800">
                        ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
                        privately.
                    </div>

                    <div class="flex items-center mb-4">
                        <input id="no-phi" name="no_phi" type="checkbox" required
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="no-phi" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            I confirm that I will not include any Protected Health Information (PHI) in this form.
                        </label>
                    </div>
                    <p class="text-xs mt-2">See our <a
                            href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}" class="underline"
                            style="color: {{ $primary }}" target="_blank" rel="noopener noreferrer">Notice of Privacy
                            Practices</a>.</p>

                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-slate-700 mb-2">Full Name
                            *</label>
                        <input id="contact_name" type="text" required autocomplete="name"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="Enter your full name">
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                        <input id="contact_phone" type="tel" inputmode="tel" autocomplete="tel"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="(555) 123-4567">
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-slate-700 mb-2">Email Address
                            *</label>
                        <input id="contact_email" type="email" required autocomplete="email"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="your@email.com">
                    </div>

                    <div>
                        <label for="contact_message" class="block text-sm font-medium text-slate-700 mb-2">Message
                            *</label>
                        <textarea id="contact_message" rows="5" required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="How can we help you today?"></textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary/30">
                            I consent to be contacted about my inquiry.
                        </label>
                        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off"> {{-- honeypot
                        --}}
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <button type="reset"
                            class="px-6 py-2.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Clear Form
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg text-white transition shadow-sm hover:shadow"
                            style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>

            {{-- Map --}}
            <div class="rounded-3xl border bg-white shadow-xl overflow-hidden h-full">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl"
                            style="background: {{ ($facility['secondary_color'] ?? '#155E75') }}1A; color: {{ $facility['secondary_color'] ?? '#155E75' }}">
                            📍</div>
                        <div>
                            <h4 class="text-base font-semibold text-slate-900">Find Us</h4>
                            <p class="text-xs text-slate-500">Get directions and plan your visit</p>
                        </div>
                    </div>
                    <a href="{{ $mapsHref }}" target="_blank" rel="noopener"
                        class="text-sm font-semibold hover:underline"
                        style="color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
                        Open in Maps →
                    </a>
                </div>
                <div>
                    @if(!empty($facility['location_map']))
                    @if(\Illuminate\Support\Str::startsWith($facility['location_map'], ['http://','https://']))
                    <iframe src="{{ $facility['location_map'] }}" class="block w-full" height="560" loading="lazy"
                        style="border:0;" allowfullscreen></iframe>
                    @else
                    {!! $facility['location_map'] !!}
                    @endif
                    @else
                    <iframe src="{{ $mapsHref }}" class="block w-full" height="560" loading="lazy" style="border:0;"
                        allowfullscreen></iframe>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>