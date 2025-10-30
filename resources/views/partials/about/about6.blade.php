<section id="about" aria-labelledby="about-variant-6" class="bg-white text-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Hero -->
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 id="about-variant-6" class="text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl lg:text-6xl"
                    style="color: {{ $primary }}">Compassionate care,
                    close to home</h2>
                <p class="mt-4 text-lg text-gray-600">At {{ $facility['name'] ?? 'Our Care Center' }}, we provide
                    respectful,
                    skilled nursing and memory care tailored to older adults and their families. Our team focuses on
                    dignity, safety, and meaningful daily life.</p>
                @if(!empty($facility['city']) || !empty($facility['state']))
                <p class="mt-2 text-sm text-gray-500">Serving {{ $facility['city'] ?? '' }}@if(!empty($facility['city'])
                    && !empty($facility['state'])), @endif{{ $facility['state'] ?? '' }} community.</p>
                @endif

                <ul class="mt-8 grid sm:grid-cols-2 gap-4">
                    <li class="flex items-start space-x-3">
                        <svg class="flex-shrink-0 h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm text-gray-700">24/7 licensed nursing staff</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="flex-shrink-0 h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-sm text-gray-700">Skilled nursing & rehabilitation</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="flex-shrink-0 h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 12c-3.314 0-6 1.79-6 4v2h12v-2c0-2.21-2.686-4-6-4z" />
                        </svg>
                        <span class="text-sm text-gray-700">Specialized memory care</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="flex-shrink-0 h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        <span class="text-sm text-gray-700">Person-centered activities & programs</span>
                    </li>
                </ul>

                <div class="mt-8 flex space-x-4">
                    <a href="#book"
                        class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Schedule
                        a tour</a>
                    <a href="#services"
                        class="inline-flex items-center px-5 py-3 border border-indigo-200 text-base font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Our
                        services</a>
                </div>

                @if(isset($facility['state']) && $facility['state'] === 'California')
                <p class="mt-6 text-sm text-gray-500">Licensed by the California Department of Public Health. We
                    accept Medicare and Medi-Cal.</p>
                @else
                <p class="mt-6 text-sm text-gray-500">{{ $facility['license_statement'] ?? 'Licensed by state health
                    authorities. We accept Medicare and Medicaid.' }}</p>
                @endif
            </div>

            <div class="relative">
                @php
                $aboutUrl = $facility['about_image_url'] ?? null;
                // default hero
                $heroSrc = $facility['images']['hero'] ?? '/build/images/nursinghome-hero.jpg';
                if ($aboutUrl) {
                // full URL or absolute path
                if (preg_match('/^https?:\/\//i', $aboutUrl) || strpos($aboutUrl, '/') === 0) {
                $heroSrc = $aboutUrl;
                } else {
                // aboutUrl is likely a filename stored in DB. Check common public locations.
                if (file_exists(public_path('images/' . $aboutUrl))) {
                $heroSrc = asset('images/' . ltrim($aboutUrl, '/'));
                } elseif (file_exists(public_path('build/images/' . $aboutUrl))) {
                $heroSrc = asset('build/images/' . ltrim($aboutUrl, '/'));
                } elseif (file_exists(public_path($aboutUrl))) {
                $heroSrc = asset(ltrim($aboutUrl, '/'));
                } else {
                // fallback: assume images folder
                $heroSrc = asset('images/' . ltrim($aboutUrl, '/'));
                }
                }
                }
                @endphp
                <div class="rounded-xl bg-gradient-to-tr from-indigo-50 to-white p-1 shadow-lg">
                    <img src="{{ $heroSrc }}" alt="Residents and staff interacting"
                        class="w-full h-64 object-cover rounded-lg sm:h-80 lg:h-96" />
                </div>
                <blockquote class="absolute -bottom-6 left-6 bg-white shadow-lg rounded-lg p-4 border border-gray-100">
                    <p class="text-sm text-gray-600">"{{ $facility['testimonial']['text'] ?? 'The compassionate staff
                        made our loved one feel at home from day one.' }}"</p>
                    <footer class="mt-2 text-xs text-gray-500">— {{ $facility['testimonial']['author'] ?? 'Family of
                        Resident' }}</footer>
                </blockquote>
            </div>
        </div>


        <!-- CTA -->
        <div class="mt-16 bg-indigo-50 rounded-lg p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h4 class="text-lg font-semibold">See the care in person</h4>
                <p class="mt-1 text-sm text-gray-600">Schedule a private tour to meet our team and experience our
                    community.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="#book"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-md font-medium hover:bg-indigo-700">Schedule
                    a tour</a>
            </div>
        </div>
    </div>
</section>