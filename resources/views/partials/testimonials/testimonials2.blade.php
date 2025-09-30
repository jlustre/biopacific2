@php
if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->find($facility['color_scheme_id']);
$primary = $scheme->primary_color ?? '#0EA5E9';
$secondary = $scheme->secondary_color ?? '#1E293B';
$accent = $scheme->accent_color ?? '#F59E0B';
} else {
$primary = '#0EA5E9';
$secondary = '#1E293B';
$accent = '#F59E0B';
}
@endphp
<section id="testimonials" class="relative py-20"
    style="background: linear-gradient(180deg, #fff 0%, {{ $primary }}11 60%, {{ $accent }}11 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold mb-4" style="color: {{ $secondary }};">What Families Are Saying</h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Genuine voices from residents and families who trust our compassionate care every day.
            </p>
        </div>

        <!-- Testimonials Grid -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            @forelse($facility->testimonials as $testimonial)
            <div
                class="bg-white shadow-lg rounded-3xl p-8 flex flex-col justify-between hover:shadow-2xl transition-all duration-300">
                <div class="mb-6">
                    @if($testimonial->title_header)
                    <div class="text-primary text-lg font-bold mb-2">{{ $testimonial->title_header }}</div>
                    @endif
                    <!-- Stars -->
                    <div class="flex items-center gap-1 mb-4 text-yellow-400">
                        @for ($i = 1; $i <= 5; $i++) <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                    </div>
                    <p class="text-teal-600 text-lg leading-relaxed italic">
                        &ldquo;{{ $testimonial->quote }}&rdquo;
                    </p>
                    @if($testimonial->story)
                    <div class="mt-3 text-slate-700 text-sm">{{ $testimonial->story }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    @if ($testimonial->photo_url)
                    <img src="{{ $testimonial->photo_url }}" alt="Testimonial Avatar"
                        class="w-14 h-14 rounded-full object-cover shadow-md" style="border: 2px solid {{ $accent }};">
                    @else
                    <svg class="w-14 h-14 text-gray-300 rounded-full bg-gray-100 shadow-md" fill="currentColor"
                        viewBox="0 0 24 24" style="border: 2px solid {{ $accent }};">
                        <path
                            d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" />
                    </svg>
                    @endif
                    <div>
                        <h4 class="font-semibold" style="color: {{ $secondary }};">{{ $testimonial->name }}</h4>
                        <span class="text-sm text-slate-500">{{ $testimonial->relationship ?? 'Family Member' }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center text-gray-500 py-12">
                <i class="fas fa-quote-right text-4xl text-gray-300 mb-4"></i>
                <p>No testimonials found for this facility.</p>
            </div>
            @endforelse
        </div>
        <!-- Call to Action -->
        <div class="mt-16 text-center">

            <p class="text-lg text-slate-700 mb-6">Want to hear more stories from our community?</p>
            <a href="#contact"
                class="inline-block bg-primary text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:bg-primary/90 transition">
                Contact Us Today
            </a>
        </div>
</section>