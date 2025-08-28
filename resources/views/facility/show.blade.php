<x-layouts.app>
    <div class="container mx-auto px-4 py-8">
        @if($facility)
            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg overflow-hidden">
                    @if($facility->hero_image_url)
                        <img src="{{ $facility->hero_image_url }}" alt="{{ $facility->name }}" class="w-full h-64 object-cover">
                    @endif

                    <div class="p-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ $facility->name }}
                        </h1>

                        @if($facility->description)
                            <div class="text-gray-600 dark:text-gray-300 mb-6">
                                {{ $facility->description }}
                            </div>
                        @endif

                        <div class="flex justify-between items-center">
                            <a href="{{ route('facilities.index') }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                ← Back to Facilities
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Facility not found</h1>
                <a href="{{ route('facilities.index') }}"
                   class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Back to Facilities
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
