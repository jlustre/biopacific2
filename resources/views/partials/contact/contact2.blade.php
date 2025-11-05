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

<section id="contact" class="relative overflow-hidden py-0">
    <!-- Hero Header -->
    <div class="relative bg-gradient-to-br from-sky-100 via-white to-amber-50 pb-12 pt-16 md:pt-24">
        <div class="mx-auto max-w-2xl text-center">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4 shadow-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.4-4 8-9 8-1.5 0-3-.3-4.3-.9L3 20l1.4-3.7A8.9 8.9 0 013 12c0-4.4 4-8 9-8s9 3.6 9 8z" />
                </svg>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Get in Touch</h2>
            <p class="mt-3 text-lg text-slate-600">We're here to answer your questions and help you every step of the
                way.</p>
        </div>
    </div>

    <!-- Responsive Grid -->
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-12 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contact Info Card -->
            <aside
                class="rounded-3xl bg-white/80 shadow-xl border border-slate-100 p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Contact Information</h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h2.6a1 1 0 01.95.69l1.3 3.9a1 1 0 01-.47 1.2l-1.8.9a10.5 10.5 0 005.3 5.3l.9-1.8a1 1 0 011.2-.47l3.9 1.3a1 1 0 01.69.95V19a2 2 0 01-2 2h-1C9.16 21 3 14.84 3 7V5z" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500">Phone</div>
                                <div class="font-semibold text-slate-900">@if($p_raw)<a href="tel:{{ $p_raw }}"
                                        class="hover:underline">{{ $p_fmt }}</a>@else N/A @endif</div>
                            </div>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.9 4.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5A2 2 0 003 7v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500">Email</div>
                                <div class="font-medium text-slate-900">@if(!empty($facility['email']))<a
                                        href="mailto:{{ $facility['email'] }}" class="hover:underline">{{
                                        $facility['email'] }}</a>@else N/A @endif</div>
                            </div>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-lime-100 text-lime-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.7 17.7L12 23l-5.7-5.3A8 8 0 1117.7 17.7z" />
                                    <circle cx="12" cy="11" r="3" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500">Address</div>
                                <div class="text-slate-900">{{ $facility['address'] ?? '' }}{{ !empty($facility['city'])
                                    ? ', ' . $facility['city'] : '' }}{{ !empty($facility['state']) ? ', ' .
                                    $facility['state'] : '' }} {{ $facility['zip'] ?? '' }}</div>
                            </div>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500">Visiting Hours</div>
                                <div class="text-slate-900">{{ $facility['hours'] ?? '10:30 AM – 8:30 PM' }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
                @if(!empty($social))
                <div class="mt-8">
                    <h4 class="text-xs font-semibold text-slate-700 mb-2">Connect With Us</h4>
                    <div class="flex flex-wrap gap-2">
                        @if(!empty($social['facebook']))
                        <a href="{{ $social['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"
                            class="h-9 w-9 flex items-center justify-center rounded-full bg-[#1877F2] text-white hover:opacity-90"><svg
                                viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                                <path
                                    d="M22 12.06C22 6.48 17.52 2 11.94 2S2 6.48 2 12.06c0 4.99 3.66 9.13 8.44 9.94v-7.03H8.08v-2.9h2.36V9.41c0-2.33 1.39-3.62 3.52-3.62.71 0 1.8.12 2.19.18v2.41h-1.24c-1.22 0-1.6.76-1.6 1.54v1.86h2.72l-.43 2.9h-2.29v7.03C18.34 21.19 22 17.05 22 12.06z" />
                            </svg></a>
                        @endif
                        @if(!empty($social['linkedin']))
                        <a href="{{ $social['linkedin'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"
                            class="h-9 w-9 flex items-center justify-center rounded-full bg-[#0A66C2] text-white hover:opacity-90"><svg
                                viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                                <path
                                    d="M6.94 6.5A2.44 2.44 0 1 1 4.5 4.06 2.44 2.44 0 0 1 6.94 6.5ZM4.75 8.5h4.39V20H4.75ZM13.1 8.5h-4.4V20h4.4v-5.8c0-3.31 4.24-3.58 4.24 0V20h4.37v-6.68c0-5.84-6.64-5.63-8.61-2.76V8.5Z" />
                            </svg></a>
                        @endif
                        @if(!empty($social['instagram']))
                        <a href="{{ $social['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"
                            class="h-9 w-9 flex items-center justify-center rounded-full bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] text-white hover:opacity-90"><svg
                                viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                                <path
                                    d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.75-2.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z" />
                            </svg></a>
                        @endif
                        @if(!empty($social['youtube']))
                        <a href="{{ $social['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"
                            class="h-9 w-9 flex items-center justify-center rounded-full bg-[#FF0000] text-white hover:opacity-90"><svg
                                viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor">
                                <path
                                    d="M23.5 7.2a4.5 4.5 0 0 0-3.15-3.18C18.55 3.5 12 3.5 12 3.5s-6.55 0-8.35.52A4.5 4.5 0 0 0 .5 7.2 47.7 47.7 0 0 0 0 12c-.02 1.62.17 3.24.5 4.8a4.5 4.5 0 0 0 3.15 3.18c1.8.52 8.35.52 8.35.52s6.55 0 8.35-.52a4.5 4.5 0 0 0 3.15-3.18c.33-1.56.5-3.18.5-4.8s-.17-3.24-.5-4.8ZM9.75 15.02V8.98l6.06 3.02-6.06 3.02Z" />
                            </svg></a>
                        @endif
                    </div>
                </div>
                @endif
            </aside>

            <!-- Contact Form Card -->
            <!-- Contact Form Card (reusable component) -->
            <div class="lg:col-span-2 flex flex-col gap-8">
                <div class="rounded-3xl bg-white/80 shadow-xl border border-slate-100 backdrop-blur overflow-hidden">
                    @include('partials.contact.contact-form', [
                    'facility' => $facility,
                    'primary' => $primary,
                    'secondary' => $secondary,
                    'accent' => $accent,
                    'neutral_dark' => '#1e293b'
                    ])
                </div>
                <!-- Map Card (collapsible on mobile) -->
                <div class="rounded-3xl bg-white/80 shadow-xl border border-slate-100 overflow-hidden mt-4">
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                📍</div>
                            <div>
                                <h4 class="text-base font-semibold text-slate-900">Find Us</h4>
                                <p class="text-xs text-slate-500">Get directions and plan your visit</p>
                            </div>
                        </div>
                        <a href="{{ $mapsHref }}" target="_blank" rel="noopener"
                            class="text-sm font-semibold hover:underline"
                            style="color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">Open in Maps →</a>
                    </div>
                    <div class="aspect-video w-full">
                        @if(!empty($facility['location_map']))
                        @if(\Illuminate\Support\Str::startsWith($facility['location_map'], ['http://','https://']))
                        <iframe src="{{ $facility['location_map'] }}" class="block w-full h-full" loading="lazy"
                            style="border:0;" allowfullscreen></iframe>
                        @else
                        {!! $facility['location_map'] !!}
                        @endif
                        @else
                        <iframe src="{{ $mapsHref }}" class="block w-full h-full" loading="lazy" style="border:0;"
                            allowfullscreen></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>