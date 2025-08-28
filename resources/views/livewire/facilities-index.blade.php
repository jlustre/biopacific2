<div class="bg-white min-h-screen py-12">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold">{{ config('app.name') }} Facilities</h1>

      @can('create facilities')
      <a href="{{ route('facilities.create') }}"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Add New Facility
      </a>
      @endcan
    </div>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
      @foreach($facilities as $facility)
      <span class="inline-flex h-9 w-9 items-center justify-center bg-primary/10 text-primary font-bold">
        <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-6 w-6 object-contain">
      </span>
      <a href="{{ route('facility.show', $facility->slug) }}"
        class="group block border rounded-lg overflow-hidden shadow hover:shadow-lg transition">
        <img src="{{ $facility->hero_image_url }}" alt="{{ $facility->name }}"
          class="h-48 w-full object-cover group-hover:scale-105 transform transition">
        <div class="p-4">
          <span class="inline-flex h-9 w-9 items-center justify-center bg-primary/10 text-primary font-bold">
            <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-6 w-6 object-contain">
          </span>
          <h2 class="text-xl font-semibold">{{ $facility->name }}</h2>
          <p class="text-gray-600">{{ $facility->city }}, {{ $facility->state }}</p>
          <p class="text-xs text-gray-500 mt-1">Domain: {{ $facility->domain ?? 'N/A' }}</p>
          <p class="text-xs text-gray-500">Phone: {{ $facility->phone ? '(' . substr($facility->phone,0,3) . ') ' .
            substr($facility->phone,3,3) . '-' . substr($facility->phone,6,4) : 'N/A' }}</p>
          <div class="mt-2 flex items-center gap-2 text-sm">
            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800">Beds: {{ $facility->beds }}</span>
          </div>
        </div>
      </a>

      @can('edit facilities')
      <div class="p-4 border-t bg-gray-50">
        <a href="{{ route('facilities.edit', $facility->id) }}"
          class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
        @can('delete facilities')
        <button class="text-red-600 hover:text-red-800 text-sm ml-4">Delete</button>
        @endcan
      </div>
      @endcan
      @endforeach
    </div>
  </div>
</div>