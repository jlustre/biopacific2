@php
// about5: compact, centered hero with features and CTA
$name = $facility['name'] ?? 'Our Care Center';
$cityState = trim(($facility['city'] ?? '') . ' ' . ($facility['state'] ?? ''));
$aboutUrl = $facility['about_image_url'] ?? null;
$hero = $facility['images']['hero'] ?? '/build/images/about-hero.png';
if ($aboutUrl) {
if (preg_match('/^https?:\/\//i', $aboutUrl) || strpos($aboutUrl, '/') === 0) {
$hero = $aboutUrl;
} else {
if (file_exists(public_path('images/' . $aboutUrl))) {
$hero = asset('images/' . ltrim($aboutUrl, '/'));
} elseif (file_exists(public_path('build/images/' . $aboutUrl))) {
$hero = asset('build/images/' . ltrim($aboutUrl, '/'));
} elseif (file_exists(public_path($aboutUrl))) {
$hero = asset(ltrim($aboutUrl, '/'));
} else {
$hero = asset('images/' . ltrim($aboutUrl, '/'));
}
}
}
@endphp

<section id="about" aria-labelledby="about-variant-5" class="bg-gray-50 text-gray-800 pt-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <h2 id="about-variant-5" class="text-2xl font-extrabold sm:text-3xl md:text-4xl" style="color: {{ $primary }}">
            About {{
            $name }}</h2>
        @if($cityState)
        <p class="mt-2 text-sm text-gray-600">Serving the {{ $cityState }} community</p>
        @endif

        <div class="mt-6 relative rounded-lg overflow-hidden">
            <img src="{{ $hero }}" alt="About image" class="w-full h-56 object-cover sm:h-72 lg:h-80" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <div class="absolute left-6 bottom-6 text-left text-white">
                <h3 class="text-lg font-semibold">Compassionate care, every day</h3>
                <p class="mt-1 text-sm opacity-90">{{ $facility['tagline'] ?? 'Skilled nursing, memory care, and
                    rehabilitation.' }}</p>
            </div>
        </div>

        <div class="mt-8 grid sm:grid-cols-3 gap-6">
            <div
                class="rounded-xl p-6 bg-gradient-to-tr from-white to-indigo-50 border border-gray-100 shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-indigo-600 text-white flex items-center justify-center shadow">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 12c-3.314 0-6 1.79-6 4v2h12v-2c0-2.21-2.686-4-6-4z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800">Care</h4>
                        <p class="mt-2 text-sm text-gray-600">{{ $facility['feature_1'] ?? '24/7 licensed nursing' }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl p-6 bg-gradient-to-tr from-white to-green-50 border border-gray-100 shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-green-600 text-white flex items-center justify-center shadow">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7v4a4 4 0 004 4h0a4 4 0 004-4V7" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800">Community</h4>
                        <p class="mt-2 text-sm text-gray-600">{{ $facility['feature_2'] ?? 'Person-centered activities'
                            }}</p>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl p-6 bg-gradient-to-tr from-white to-yellow-50 border border-gray-100 shadow-sm hover:shadow-lg transition transform hover:-translate-y-1">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-yellow-500 text-white flex items-center justify-center shadow">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v4" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800">Rehab</h4>
                        <p class="mt-2 text-sm text-gray-600">{{ $facility['feature_3'] ?? 'On-site therapy services' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="max-w-3xl mx-auto">
                <div id="about5-tabs" class="border rounded-lg bg-white" aria-live="polite">
                    <div role="tablist" aria-label="About tabs"
                        class="flex flex-col sm:flex-row sm:space-x-1 bg-white p-2 rounded-t-lg">
                        <!-- visually enhanced tabs -->
                        <button id="about5-tab-mission" role="tab" aria-controls="about5-panel-mission"
                            aria-selected="true" data-tab="mission"
                            class="flex-1 px-4 py-3 rounded-md text-sm font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 shadow-sm text-left">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <div>Mission</div>
                                    <div class="text-xs text-gray-500">Why we exist</div>
                                </div>
                            </div>
                        </button>

                        <button id="about5-tab-vision" role="tab" aria-controls="about5-panel-vision"
                            aria-selected="false" data-tab="vision"
                            class="mt-2 sm:mt-0 flex-1 px-4 py-3 rounded-md text-sm font-semibold text-gray-600 bg-white border border-gray-100 hover:shadow-sm text-left">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 12c-3.314 0-6 1.79-6 4v2h12v-2c0-2.21-2.686-4-6-4z" />
                                </svg>
                                <div>
                                    <div>Vision</div>
                                    <div class="text-xs text-gray-500">Where we're headed</div>
                                </div>
                            </div>
                        </button>

                        <button id="about5-tab-values" role="tab" aria-controls="about5-panel-values"
                            aria-selected="false" data-tab="values"
                            class="mt-2 sm:mt-0 flex-1 px-4 py-3 rounded-md text-sm font-semibold text-gray-600 bg-white border border-gray-100 hover:shadow-sm text-left">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <div>
                                    <div>Values</div>
                                    <div class="text-xs text-gray-500">What we stand for</div>
                                </div>
                            </div>
                        </button>
                    </div>

                    <div class="p-6">
                        <!-- Mission panel: include a short stat row + expanded copy -->
                        <div id="about5-panel-mission" role="tabpanel" aria-labelledby="about5-tab-mission">
                            <p class="text-sm text-gray-600">{{ $facility['mission'] ?? 'We are committed to delivering
                                compassionate, high-quality care that respects the dignity of every resident.' }}</p>

                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-xl font-bold text-indigo-700">{{ $facility['licensed_beds'] ?? '—'
                                        }}</div>
                                    <div class="text-xs text-gray-500">Licensed beds</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-bold text-indigo-700">{{ $facility['nursing_coverage'] ??
                                        '24/7' }}</div>
                                    <div class="text-xs text-gray-500">Nursing coverage</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-bold text-indigo-700">{{ $facility['satisfaction_rating']
                                        ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">Family rating</div>
                                </div>
                            </div>
                        </div>

                        <!-- Vision panel: timeline style -->
                        <div id="about5-panel-vision" role="tabpanel" aria-labelledby="about5-tab-vision" hidden
                            class="hidden">
                            <p class="text-sm text-gray-600">{{ $facility['vision'] ?? 'To be the trusted leader in
                                senior care, fostering independence and well-being.' }}</p>

                            <ol class="mt-4 border-l-2 border-gray-100 pl-4 space-y-3">
                                <li>
                                    <div class="text-xs text-gray-500">Founded</div>
                                    <div class="text-sm font-medium">{{ $facility['founded'] ?? '1998' }}</div>
                                </li>
                                <li>
                                    <div class="text-xs text-gray-500">Accreditations</div>
                                    <div class="text-sm font-medium">{{ $facility['accreditations'] ?? 'Licensed, CMS
                                        certified' }}</div>
                                </li>
                                <li>
                                    <div class="text-xs text-gray-500">Looking ahead</div>
                                    <div class="text-sm font-medium">{{ $facility['future_focus'] ?? 'Stronger family
                                        partnerships and enhanced rehabilitation programs.' }}</div>
                                </li>
                            </ol>
                        </div>

                        <!-- Values panel: card layout -->
                        <div id="about5-panel-values" role="tabpanel" aria-labelledby="about5-tab-values" hidden
                            class="hidden">
                            <div class="mt-2 grid sm:grid-cols-2 gap-4">
                                @php
                                $values = $facility['values'] ?? null;
                                if (is_string($values)) {
                                $values_list = array_filter(array_map('trim', preg_split('/[\r\n]+/', $values)));
                                } elseif (is_array($values)) {
                                $values_list = $values;
                                } else {
                                $values_list = ['Respect', 'Compassion', 'Integrity'];
                                }
                                @endphp

                                @foreach($values_list as $val)
                                <div class="bg-white border rounded-lg p-4">
                                    <div class="text-sm font-semibold text-gray-800">{{ $val }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $facility['value_descriptions'][$val] ??
                                        ($facility['value_descriptions'][$loop->index] ?? 'We prioritize this value in
                                        daily care and interactions.') }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function(){
            const container = document.getElementById('about5-tabs');
            if (!container) return;
            const tabs = Array.from(container.querySelectorAll('[role="tab"]'));
            const panels = Array.from(container.querySelectorAll('[role="tabpanel"]'));

            function activateTab(tab) {
                tabs.forEach(t => {
                    const selected = t === tab;
                    t.setAttribute('aria-selected', selected ? 'true' : 'false');

                    if (selected) {
                        // add selected styles
                        t.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-100');
                        t.classList.add('bg-indigo-50', 'text-indigo-700', 'border', 'border-indigo-100', 'shadow-sm');
                    } else {
                        // revert to unselected styles
                        t.classList.remove('bg-indigo-50', 'text-indigo-700', 'border', 'border-indigo-100', 'shadow-sm');
                        t.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-100');
                    }
                });

                panels.forEach(p => {
                    const show = p.id === tab.getAttribute('aria-controls');
                    p.hidden = !show;
                    p.classList.toggle('hidden', !show);
                });

                tab.focus();
            }

            tabs.forEach((t, i) => {
                t.addEventListener('click', () => activateTab(t));
                t.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                        e.preventDefault();
                        const dir = e.key === 'ArrowRight' ? 1 : -1;
                        const next = tabs[(i + dir + tabs.length) % tabs.length];
                        activateTab(next);
                    }
                });
            });

            const initial = container.querySelector('[role="tab"][aria-selected="true"]') || tabs[0];
            activateTab(initial);
        })();
        </script>

        <div class="mt-8">
            <a href="#book"
                class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-md font-medium hover:bg-indigo-700">Schedule
                a tour</a>
        </div>
    </div>
</section>