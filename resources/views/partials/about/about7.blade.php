@php
// Simple, fresh about7 variant - clean and responsive
$name = $facility['name'] ?? 'Our Care Center';
$heroUrl = $facility['about_image_url'] ?? ($facility['images']['hero'] ?? '/build/images/hero6.png');
// resolve filenames in public/images
if ($heroUrl && !preg_match('/^https?:\/\//i', $heroUrl) && strpos($heroUrl, '/') !== 0) {
if (file_exists(public_path('images/' . $heroUrl))) $heroUrl = asset('images/' . ltrim($heroUrl, '/'));
elseif (file_exists(public_path('build/images/' . $heroUrl))) $heroUrl = asset('build/images/' . ltrim($heroUrl, '/'));
}

$stats = [
'Licensed beds' => $facility['licensed_beds'] ?? ($facility['beds'] ?? 25),
'Nursing' => $facility['nursing_coverage'] ?? '24/7',
'Family rating' => $facility['satisfaction_rating'] ?? '4.8/5',
];

$values = [
['title' => 'Person-Centered Care', 'desc' => 'We tailor care plans to each resident’s needs and goals.'],
['title' => 'Safety', 'desc' => 'We maintain high safety and infection-control standards.'],
['title' => 'Dignity', 'desc' => 'We treat residents with respect and encourage independence.'],
];

$testimonials = $facility['testimonials'] ?? ($facility['testimonial'] ? [$facility['testimonial']] : [
['text' => 'We felt welcome and cared for from day one.', 'author' => 'Family member']
]);
@endphp

<section id="about" class="bg-white text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold">About <span style="color: {{ $primary }}">{{
                        $name }}</span></h2>
                }}</h2>
                <p class="mt-4 text-lg" style="color: {{ $secondary }}">{{ $facility['short_description'] ??
                    'Compassionate,
                    resident-focused care.' }}</p>

                <div class="mt-6 grid grid-cols-3 gap-4">
                    @foreach($stats as $label => $value)
                    <div class="bg-indigo-50 rounded-lg p-4 text-center">
                        <div class="text-indigo-700 font-bold text-xl">{{ $value }}</div>
                        <div class="text-xs text-indigo-600 mt-1">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                <!-- Tabs: Mission / Vision / Values -->
                <div x-data="{tab: 'mission'}" class="mt-8">
                    <div role="tablist" aria-label="Our mission vision values" class="flex space-x-2">
                        <button @click="tab='mission'"
                            :class="tab==='mission' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-700'"
                            class="px-4 py-2 rounded">Mission</button>
                        <button @click="tab='vision'"
                            :class="tab==='vision' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-700'"
                            class="px-4 py-2 rounded">Vision</button>
                        <button @click="tab='values'"
                            :class="tab==='values' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-700'"
                            class="px-4 py-2 rounded">Values</button>
                    </div>

                    <div class="mt-4 bg-white p-6 rounded-lg shadow-sm">
                        <div x-show="tab==='mission'">
                            <h3 class="text-lg font-semibold">Mission</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $facility['mission'] ?? 'Delivering compassionate
                                care that preserves dignity and promotes independence.' }}</p>
                        </div>

                        <div x-show="tab==='vision'" x-cloak>
                            <h3 class="text-lg font-semibold">Vision</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $facility['vision'] ?? 'To be the trusted choice
                                for quality senior care.' }}</p>
                        </div>

                        <div x-show="tab==='values'" x-cloak>
                            <h3 class="text-lg font-semibold">Values</h3>
                            <ul class="mt-3 space-y-2">
                                @foreach($values as $v)
                                @php
                                $t = is_string($v) ? $v : (is_array($v) ? ($v['title'] ?? $v['name'] ?? '') : ($v->title
                                ?? ''));
                                @endphp
                                <li class="flex items-center gap-3">
                                    <span
                                        class="h-8 w-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="text-indigo-700">{{ $t ?: 'Our Value' }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="rounded-2xl overflow-hidden shadow-lg">
                    <img src="{{ $heroUrl }}" alt="About image" class="w-full h-96 object-cover" />
                </div>
                @php
                $bookUrl = '#book';
                $contactUrl = '#contact';
                @endphp

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ $bookUrl }}" @if(preg_match('/^https?:\\/\\//i', $bookUrl)) target="_blank"
                        rel="noopener" @endif
                        class="inline-flex items-center justify-center px-5 py-3 bg-indigo-600 text-white font-medium rounded-lg shadow hover:bg-indigo-700 transition">
                        Book A Tour
                    </a>

                    <a href="{{ $contactUrl }}" @if(preg_match('/^https?:\\/\\//i', $contactUrl)) target="_blank"
                        rel="noopener" @endif
                        class="inline-flex items-center justify-center px-5 py-3 bg-white border border-indigo-600 text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>