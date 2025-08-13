<div class="bg-white min-h-screen py-12">
  <div class="max-w-6xl mx-auto px-4">
    <h1 class="text-3xl font-bold mb-8 text-center">{{ config('app.name') }} Facilities</h1>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
      @foreach($facilities as $facility)
        <a href="{{ route('facility.show', $facility->slug) }}" class="group block border rounded-lg overflow-hidden shadow hover:shadow-lg transition">
          <img src="{{ $facility->hero_image_url }}" alt="{{ $facility->name }}" class="h-48 w-full object-cover group-hover:scale-105 transform transition">
          <div class="p-4">
            <h2 class="text-xl font-semibold">{{ $facility->name }}</h2>
            <p class="text-gray-600">{{ $facility->city }}, {{ $facility->state }}</p>
            <div class="mt-2 flex items-center gap-2 text-sm">
              <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800">Beds: {{ $facility->beds }}</span>
              @if($facility->ranking_position && $facility->ranking_total)
                <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">Rank: {{ $facility->ranking_position }} / {{ $facility->ranking_total }}</span>
              @endif
            </div>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</div>

