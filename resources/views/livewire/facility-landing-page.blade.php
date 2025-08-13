<div class="bg-white">
    @push('meta')
        <script type="application/ld+json">
        {
        "@context": "https://schema.org",
        "@type": "NursingHome",
        "name": "{{ $facility->name }}",
        "url": "{{ route('facility.show', $facility) }}",
        "image": "{{ $facility->hero_image_url }}",
        "telephone": "{{ $facility->phone }}",
        "email": "{{ $facility->email }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ $facility->address }}",
            "addressLocality": "{{ $facility->city }}",
            "addressRegion": "{{ $facility->state }}",
            "addressCountry": "US"
        }
        }
        </script>
    @endpush

  <section class="relative">
    <img src="{{ $facility->hero_image_url }}" class="w-full h-96 object-cover" alt="{{ $facility->name }}">
    <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center text-center text-white">
      <img src="{{ $facility->logo_url }}" class="h-20 w-20 rounded-full mb-4 shadow-lg" alt="Logo">
      <h1 class="text-4xl font-bold">{{ $facility->name }}</h1>
      <p class="mt-2 text-lg">{{ $facility->headline }}</p>
      <div class="mt-4 flex gap-2 text-sm">
        <span class="bg-white/90 text-gray-800 px-3 py-1 rounded">Beds: {{ $facility->beds }}</span>
        @if($facility->ranking_position && $facility->ranking_total)
          <span class="bg-indigo-600/90 px-3 py-1 rounded">Ranking: {{ $facility->ranking_position }} / {{ $facility->ranking_total }}</span>
        @endif
        @if($facility->ownership_role)
          <span class="bg-emerald-600/90 px-3 py-1 rounded">{{ $facility->ownership_role }}</span>
        @endif
      </div>
    </div>
  </section>

  <section class="py-12 max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-8 items-center">
    <img src="{{ $facility->about_image_url }}" class="rounded-lg shadow-md" alt="About">
    <div>
      <h2 class="text-2xl font-bold mb-3">About Us</h2>
      <p class="text-gray-600">{{ $facility->about_text }}</p>
      <ul class="mt-6 grid grid-cols-2 gap-3">
        @foreach($facility->values as $v)
          <li class="bg-gray-100 rounded px-3 py-2 text-center font-medium">{{ $v->value }}</li>
        @endforeach
      </ul>
    </div>
  </section>

  <section class="py-12 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl font-bold mb-6 text-center">Our Services</h2>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($facility->services as $s)
          <div class="p-6 bg-white border rounded-lg text-center">
            {!! $s->icon !!}
            <h3 class="mt-3 font-semibold">{{ $s->title }}</h3>
            <p class="mt-2 text-gray-600">{{ $s->description }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="py-12">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl font-bold mb-6 text-center">What Families Say</h2>
      <div class="grid md:grid-cols-3 gap-6">
        @foreach($facility->testimonials as $t)
          <div class="p-6 bg-white rounded-lg shadow text-center">
            <img src="{{ $t->photo_url }}" alt="{{ $t->name }}" class="w-16 h-16 rounded-full mx-auto mb-3">
            <p class="italic text-gray-700">"{{ $t->quote }}"</p>
            <p class="mt-2 font-semibold">{{ $t->name }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="py-12 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl font-bold mb-6 text-center">Gallery</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($facility->galleryImages as $img)
          <img src="{{ $img->thumbnail_url }}" class="rounded-lg shadow hover:opacity-90 transition" alt="Gallery image">
        @endforeach
      </div>
    </div>
  </section>

  <footer class="bg-gray-900 text-gray-300 py-10">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-4 gap-8">
      <div class="col-span-2">
        <h3 class="text-white font-bold">{{ $facility->name }}</h3>
        <p class="mt-2">{{ $facility->address }}</p>
        <p>{{ $facility->city }}, {{ $facility->state }}</p>
        <p class="mt-2">{{ $facility->phone }} • {{ $facility->email }}</p>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-2">Follow</h4>
        <ul class="space-y-1">
          <li><a class="hover:text-white" href="{{ $facility->facebook }}">Facebook</a></li>
          <li><a class="hover:text-white" href="{{ $facility->twitter }}">Twitter</a></li>
          <li><a class="hover:text-white" href="{{ $facility->instagram }}">Instagram</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-2">Contact</h4>
        <a href="tel:{{ preg_replace('/[^0-9]/','', $facility->phone) }}" class="hover:text-white block">Call Us</a>
        <a href="mailto:{{ $facility->email }}" class="hover:text-white block">Email Us</a>
      </div>
    </div>
  </footer>
</div>

