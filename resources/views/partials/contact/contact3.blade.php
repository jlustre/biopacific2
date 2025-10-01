@php
$scheme = isset($facility['color_scheme_id']) ? \DB::table('color_schemes')->find($facility['color_scheme_id']) : null;
$primary = $primary ?? ($scheme->primary_color ?? '#0EA5E9');
$secondary = $secondary ?? ($scheme->secondary_color ?? '#1E293B');
$accent = $accent ?? ($scheme->accent_color ?? '#F59E0B');
$mapsEmbed = $facility['location_map'] ?? null;
$mapsHref = (isset($mapsEmbed) && \Illuminate\Support\Str::startsWith($mapsEmbed, ['http://','https://']))
? $mapsEmbed
: 'https://www.google.com/maps?q=' . urlencode(trim(($facility['address']??'').' '.($facility['city']??'').'
'.($facility['state']??'').' '.($facility['zip']??'')));
$p_raw = preg_replace('/\D/','', $facility['phone'] ?? '');
$p_fmt = $p_raw ? sprintf('(%s) %s-%s', substr($p_raw,0,3), substr($p_raw,3,3), substr($p_raw,6,4)) : 'N/A';
$social = $facility['social'] ?? [];
@endphp

<section id="contact" class="relative min-h-screen flex flex-col lg:flex-row">
    <!-- Left: Color-blocked Info -->
    <div
        class="w-full lg:w-1/2 flex flex-col justify-between bg-gradient-to-b from-amber-100 via-white to-sky-100 p-8 lg:p-16 relative">
        <div>
            <h2 class="text-4xl font-extrabold text-amber-700 mb-4">Contact Us</h2>
            <p class="text-lg text-slate-700 mb-8">We'd love to hear from you. Reach out with questions, feedback, or to
                plan your visit.</p>
            <ul class="space-y-6 mb-8">
                <li class="flex items-center gap-4">
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-200 text-amber-700 text-2xl">📞</span>
                    <div>
                        <div class="text-xs text-slate-500">Phone</div>
                        <div class="font-semibold text-slate-900">@if($p_raw)<a href="tel:{{ $p_raw }}"
                                class="hover:underline">{{ $p_fmt }}</a>@else N/A @endif</div>
                    </div>
                </li>
                <li class="flex items-center gap-4">
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-200 text-sky-700 text-2xl">✉️</span>
                    <div>
                        <div class="text-xs text-slate-500">Email</div>
                        <div class="font-medium text-slate-900">@if(!empty($facility['email']))<a
                                href="mailto:{{ $facility['email'] }}" class="hover:underline">{{ $facility['email']
                                }}</a>@else N/A @endif</div>
                    </div>
                </li>
                <li class="flex items-center gap-4">
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-lime-200 text-lime-700 text-2xl">📍</span>
                    <div>
                        <div class="text-xs text-slate-500">Address</div>
                        <div class="text-slate-900">{{ $facility['address'] ?? '' }}{{ !empty($facility['city']) ? ', '
                            . $facility['city'] : '' }}{{ !empty($facility['state']) ? ', ' . $facility['state'] : '' }}
                            {{ $facility['zip'] ?? '' }}</div>
                    </div>
                </li>
                <li class="flex items-center gap-4">
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-200 text-slate-700 text-2xl">⏰</span>
                    <div>
                        <div class="text-xs text-slate-500">Visiting Hours</div>
                        <div class="text-slate-900">{{ $facility['hours'] ?? '10:30 AM – 8:30 PM' }}</div>
                    </div>
                </li>
            </ul>
            @if(!empty($social))
            <div class="mb-8">
                <h4 class="text-xs font-semibold text-slate-700 mb-2">Connect With Us</h4>
                <div class="flex flex-wrap gap-2">
                    @if(!empty($social['facebook']))
                    <a href="{{ $social['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"
                        class="h-10 w-10 flex items-center justify-center rounded-full bg-[#1877F2] text-white hover:opacity-90"><svg
                            viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                            <path
                                d="M22 12.06C22 6.48 17.52 2 11.94 2S2 6.48 2 12.06c0 4.99 3.66 9.13 8.44 9.94v-7.03H8.08v-2.9h2.36V9.41c0-2.33 1.39-3.62 3.52-3.62.71 0 1.8.12 2.19.18v2.41h-1.24c-1.22 0-1.6.76-1.6 1.54v1.86h2.72l-.43 2.9h-2.29v7.03C18.34 21.19 22 17.05 22 12.06z" />
                        </svg></a>
                    @endif
                    @if(!empty($social['linkedin']))
                    <a href="{{ $social['linkedin'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"
                        class="h-10 w-10 flex items-center justify-center rounded-full bg-[#0A66C2] text-white hover:opacity-90"><svg
                            viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                            <path
                                d="M6.94 6.5A2.44 2.44 0 1 1 4.5 4.06 2.44 2.44 0 0 1 6.94 6.5ZM4.75 8.5h4.39V20H4.75ZM13.1 8.5h-4.4V20h4.4v-5.8c0-3.31 4.24-3.58 4.24 0V20h4.37v-6.68c0-5.84-6.64-5.63-8.61-2.76V8.5Z" />
                        </svg></a>
                    @endif
                    @if(!empty($social['instagram']))
                    <a href="{{ $social['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"
                        class="h-10 w-10 flex items-center justify-center rounded-full bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] text-white hover:opacity-90"><svg
                            viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                            <path
                                d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.75-2.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z" />
                        </svg></a>
                    @endif
                    @if(!empty($social['youtube']))
                    <a href="{{ $social['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"
                        class="h-10 w-10 flex items-center justify-center rounded-full bg-[#FF0000] text-white hover:opacity-90"><svg
                            viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                            <path
                                d="M23.5 7.2a4.5 4.5 0 0 0-3.15-3.18C18.55 3.5 12 3.5 12 3.5s-6.55 0-8.35.52A4.5 4.5 0 0 0 .5 7.2 47.7 47.7 0 0 0 0 12c-.02 1.62.17 3.24.5 4.8a4.5 4.5 0 0 0 3.15 3.18c1.8.52 8.35.52 8.35.52s6.55 0 8.35-.52a4.5 4.5 0 0 0 3.15-3.18c.33-1.56.5-3.18.5-4.8s-.17-3.24-.5-4.8ZM9.75 15.02V8.98l6.06 3.02-6.06 3.02Z" />
                        </svg></a>
                    @endif
                </div>
            </div>
            @endif
            <div class="mt-auto">
                <div class="flex items-center gap-3 mb-2 mt-6">
                    <span
                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-lime-200 text-lime-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 21c-4.418 0-8-5.373-8-10a8 8 0 1116 0c0 4.627-3.582 10-8 10zm0-7a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold text-slate-800">Our Location</span>
                </div>
            </div>
            @if(!empty($mapsEmbed))
            <div class="rounded-2xl overflow-hidden shadow mt-2">
                <iframe src="{{ $mapsEmbed }}" width="100%" height="220" style="border:0;" allowfullscreen=""
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            @else
            <div class="rounded-2xl overflow-hidden shadow mt-2">
                <iframe width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.google.com/maps?q={{ urlencode(trim(($facility['address']??'').' '.($facility['city']??'').' '.($facility['state']??'').' '.($facility['zip']??''))) }}&output=embed">
                </iframe>
            </div>
            @endif
        </div>
    </div>
    </div>
    <!-- Right: Floating Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-white/80 relative p-6 lg:p-16 min-h-[60vh]">
        <div
            class="w-full max-w-xl mx-auto rounded-3xl shadow-2xl border border-slate-100 bg-white/90 p-6 sm:p-10 backdrop-blur-lg">
            <form class="space-y-6">
                <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800 mb-2">
                    ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
                    privately.
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-slate-700 mb-1">Full Name
                            *</label>
                        <input id="contact_name" type="text" required autocomplete="name"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="Enter your full name">
                    </div>
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input id="contact_phone" type="tel" inputmode="tel" autocomplete="tel"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="(555) 123-4567">
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-slate-700 mb-1">Email Address
                            *</label>
                        <input id="contact_email" type="email" required autocomplete="email"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="your@email.com">
                    </div>
                    <div class="md:col-span-2">
                        <label for="contact_message" class="block text-sm font-medium text-slate-700 mb-1">Message
                            *</label>
                        <textarea id="contact_message" rows="5" required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="How can we help you today?"></textarea>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4 mt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary/30">
                        I consent to be contacted about my inquiry.
                    </label>
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off"> <!-- honeypot -->
                </div>
                <div class="flex flex-col gap-2 mt-2">
                    <div class="flex items-center">
                        <input id="no-phi" name="no_phi" type="checkbox" required
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="no-phi" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">I confirm that I
                            will not include any Protected Health Information (PHI) in this form.</label>
                    </div>
                    <p class="text-xs">See our <a href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}"
                            class="underline" style="color: {{ $primary }}" target="_blank"
                            rel="noopener noreferrer">Notice of Privacy Practices</a>.</p>
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-3 mt-4">
                    <button type="reset"
                        class="px-6 py-2.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">Clear
                        Form</button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg text-white transition shadow-sm hover:shadow"
                        style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</section>