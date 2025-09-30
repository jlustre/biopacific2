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
$testimonials = $facility->testimonials ?? collect();
@endphp

<section id="testimonials" class="relative py-20"
    style="background: linear-gradient(180deg, #fff 0%, {{ $primary }}11 60%, {{ $accent }}11 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold mb-4" style="color: {{ $secondary }};">What People Are Saying</h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">Real stories and feedback from our residents and their
                families.</p>
        </div>
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
            @forelse($testimonials as $testimonial)
            <div
                class="relative bg-white shadow-xl rounded-3xl p-10 flex flex-col items-center text-center hover:shadow-2xl transition-all duration-300">
                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-primary rounded-full p-3 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M7.17 6.17A5.97 5.97 0 0 0 2 12c0 3.31 2.69 6 6 6 1.66 0 3-1.34 3-3 0-1.31-1.06-2.38-2.37-2.49C8.89 11.13 10 9.7 10 8c0-1.66-1.34-3-3-3zm10 0A5.97 5.97 0 0 0 12 12c0 3.31 2.69 6 6 6 1.66 0 3-1.34 3-3 0-1.31-1.06-2.38-2.37-2.49C18.89 11.13 20 9.7 20 8c0-1.66-1.34-3-3-3z" />
                    </svg>
                </div>
                @if($testimonial->photo_url)
                <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}"
                    class="w-20 h-20 rounded-full object-cover border-4 border-white shadow -mt-8 mb-4">
                @else
                <svg class="w-20 h-20 text-gray-300 rounded-full bg-gray-100 border-4 border-white shadow -mt-8 mb-4"
                    fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" />
                </svg>
                @endif
                @if($testimonial->title_header)
                <div class="text-primary text-lg font-bold mb-1">{{ $testimonial->title_header }}</div>
                @endif
                <h3 class="text-2xl font-serif font-semibold text-teal-600 leading-snug mb-4">&ldquo;{{
                    $testimonial->quote }}&rdquo;</h3>
                @if($testimonial->story)
                <div class="mb-3 text-slate-700 text-base">{{ $testimonial->story }}</div>
                @endif
                <div class="flex items-center justify-center gap-2 mb-2">
                    @for ($i = 1; $i <= 5; $i++) <svg
                        class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-slate-300' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        @endfor
                </div>
                <div class="text-slate-800 font-semibold text-lg">{{ $testimonial->name }}</div>
                <div class="text-slate-500 text-sm mb-2">{{ $testimonial->relationship ?? 'Family Member' }}</div>
            </div>
            @empty
            <div class="col-span-3 text-center text-gray-500 py-12">
                <i class="fas fa-quote-right text-4xl text-gray-300 mb-4"></i>
                <p>No testimonials found for this facility.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-16 text-center">
            <p class="text-lg text-slate-700 mb-6">Want to hear more stories from our community?</p>
            <a href="#contact"
                class="inline-block bg-teal-500 text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:bg-teal-400 transition">
                Contact Us Today
            </a>
        </div>
    </div>
</section>