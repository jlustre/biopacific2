@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';

// Example data; replace with DB/CMS
$quickQuotes = [
['text' => 'Compassionate staff and spotless facility.', 'name'=>'Elena (Daughter)'],
['text' => 'Therapy team helped Dad walk again.', 'name'=>'Dr. Chen (Son)'],
['text' => 'Feels like home—Mom is thriving.', 'name'=>'Sarah (Family)'],
['text' => 'Clear updates and great communication.', 'name'=>'Marcos (Son)'],
];

$stories = [
[
'name'=>'Patel Family','role'=>'Family',
'avatar'=>'https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?w=120&h=120&fit=crop&crop=faces',
'rating'=>5,
'title'=>'“Warm welcome from day one”',
'text'=>'From admission to daily updates, the team guided us with kindness. Discharge planning after rehab was smooth
and transparent.',
'media'=>['type'=>'image','src'=>'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1200&fit=crop']
],
[
'name'=>'Walter H.','role'=>'Resident',
'avatar'=>'',
'rating'=>5,
'title'=>'“Stronger every week”',
'text'=>'PT/OT helped me regain balance and confidence. Staff check on me often—I feel safe and cared for.',
'media'=>['type'=>'image','src'=>'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1200&fit=crop']
],
[
'name'=>'Dr. Lopez','role'=>'Physician',
'avatar'=>'https://images.unsplash.com/photo-1557862921-37829c790f19?w=120&h=120&fit=crop&crop=faces',
'rating'=>5,
'title'=>'“Clinical and compassionate”',
'text'=>'Evidence-based protocols, timely escalations, and family-centered goals—excellent multidisciplinary
coordination.',
'media'=>['type'=>'video','src'=>'https://www.youtube.com/embed/dQw4w9WgXcQ'] // replace
],
[
'name'=>'Evelyn R.','role'=>'Resident',
'avatar'=>'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=120&h=120&fit=crop&crop=faces',
'rating'=>4,
'title'=>'“Activities I look forward to”',
'text'=>'Bingo, crafts, and music keep my week full. Dining team respects my preferences and diet.',
'media'=>['type'=>'image','src'=>'https://images.unsplash.com/photo-1517664631085-eba10878a6b1?q=80&w=1200&fit=crop']
],
];
@endphp

<section id="testimonials" class="relative isolate overflow-hidden py-16 sm:py-24"
    style="--primary: {{ $primary }}; --secondary: {{ $secondary }}; --accent: {{ $accent }};">
    {{-- Soft ambient background --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: var(--primary)"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: var(--accent)"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/70"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                style="color: var(--primary); border-color: var(--primary);">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: var(--accent)"></span>
                Trusted by Families & Residents
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold text-slate-900">Testimonials</h2>
            <p class="mt-2 text-slate-600 md:text-lg">A look into real experiences—clinical results, daily life, and
                heartfelt moments.</p>
        </div>

        {{-- Marquee strip of quick quotes --}}
        <div class="mt-8 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200">
            <div class="relative whitespace-nowrap">
                <div
                    class="animate-[marquee_28s_linear_infinite] hover:[animation-play-state:paused] py-4 flex items-center gap-8 px-4">
                    @foreach($quickQuotes as $q)
                    <div class="inline-flex items-center gap-3">
                        <svg class="h-5 w-5 text-[color:var(--primary)]" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M9.5 7.5h-3A3.5 3.5 0 003 11v6h6v-6H6.5A1.5 1.5 0 015 9.5v-2zm11 0h-3A3.5 3.5 0 0015 11v6h6v-6h-2.5A1.5 1.5 0 0118 9.5v-2z" />
                        </svg>
                        <span class="text-slate-700 text-sm sm:text-base">“{{ $q['text'] }}” <span
                                class="text-slate-500">— {{ $q['name'] }}</span></span>
                    </div>
                    @endforeach
                    {{-- duplicate for seamless loop --}}
                    @foreach($quickQuotes as $q)
                    <div class="inline-flex items-center gap-3">
                        <svg class="h-5 w-5 text-[color:var(--primary)]" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M9.5 7.5h-3A3.5 3.5 0 003 11v6h6v-6H6.5A1.5 1.5 0 015 9.5v-2zm11 0h-3A3.5 3.5 0 0015 11v6h6v-6h-2.5A1.5 1.5 0 0118 9.5v-2z" />
                        </svg>
                        <span class="text-slate-700 text-sm sm:text-base">“{{ $q['text'] }}” <span
                                class="text-slate-500">— {{ $q['name'] }}</span></span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Story timeline (alternating) --}}
        <div class="mt-12 relative">
            <div
                class="absolute left-4 sm:left-1/2 sm:-translate-x-1/2 top-0 bottom-0 w-px bg-gradient-to-b from-slate-200 via-slate-200 to-transparent pointer-events-none">
            </div>

            <div class="space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($stories as $s)
                    @php
                    $avatar = !empty($s['avatar']) ? $s['avatar'] : 'https://ui-avatars.com/api/?name=' .
                    urlencode($s['name']) . '&background=0EA5E9&color=fff&size=120';
                    @endphp
                    <article>
                        <div
                            class="h-full rounded-3xl bg-white ring-1 ring-slate-200 p-6 sm:p-7 shadow-sm flex flex-col">
                            <div class="flex items-start gap-4">
                                <img src="{{ $avatar }}" alt="{{ $s['name'] }}"
                                    class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $s['name'] }}</div>
                                            <div class="text-xs text-slate-500">{{ $s['role'] }}</div>
                                        </div>
                                        <div class="flex items-center">
                                            @for($r=1;$r<=5;$r++) <svg
                                                class="h-4 w-4 {{ $r <= $s['rating'] ? 'text-yellow-400' : 'text-slate-300' }}"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                @endfor
                                        </div>
                                    </div>
                                    <h3 class="mt-3 text-lg font-bold text-slate-900">{!! $s['title'] !!}</h3>
                                </div>
                            </div>
                            <p class="mt-3 text-slate-700 leading-relaxed">{{ $s['text'] }}</p>
                            <div class="mt-5 flex items-center justify-between">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] ring-1 ring-slate-200"
                                    style="color: var(--primary)">Verified Review</span>
                                <button x-data @click="$dispatch('open-testimonial',{ payload: @js($s) })"
                                    class="text-sm font-semibold hover:underline" style="color: var(--primary)">Read
                                    full</button>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div
            class="mt-12 rounded-3xl bg-white ring-1 ring-slate-200 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="text-lg font-bold text-slate-900">See our care in person</div>
                <p class="text-sm text-slate-600">Book a tour to meet the team and explore our community.</p>
            </div>
            <div class="flex gap-3">
                <a href="#book"
                    class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow"
                    style="background: var(--primary)">Book a Tour</a>
                <a href="#contact" class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold ring-2"
                    style="color: var(--primary); border-color: var(--primary)">Contact Us</a>
            </div>
        </div>
    </div>

    {{-- Modal (no external JS needed) --}}
    <div x-cloak x-data="{ open:false, item:{} }" x-on:open-testimonial.window="open=true; item=$event.detail.payload"
        x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
        @click.self="open=false">
        <div class="max-w-2xl w-full rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <img :src="item.avatar" alt="" class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-slate-900" x-text="item.name"></div>
                                <div class="text-xs text-slate-500" x-text="item.role"></div>
                            </div>
                            <div class="flex items-center">
                                <template x-for="i in 5">
                                    <svg class="h-4 w-4"
                                        :class="i <= (item.rating||0) ? 'text-yellow-400' : 'text-slate-300'"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </template>
                            </div>
                        </div>
                        <h3 class="mt-3 text-lg font-bold text-slate-900" x-text="item.title"></h3>
                        <p class="mt-3 text-slate-700 leading-relaxed" x-text="item.text"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="open=false"
                        class="rounded-xl bg-slate-100 hover:bg-slate-200 px-4 py-2 text-sm font-semibold">Close</button>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Hide elements with x-cloak until Alpine.js is loaded */
    [x-cloak] {
        display: none !important;
    }

    @keyframes marquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }
</style>