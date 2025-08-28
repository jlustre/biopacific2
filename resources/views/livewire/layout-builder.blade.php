<div>
    <div class="mb-6 p-6 rounded-xl bg-white/80 shadow-lg">
        <h1 class="text-5xl font-bold text-green-700">{{ $facility?->headline ?? 'Default Heading' }}</h1>
        <p class="text-xl text-gray-600 mt-2">{{ $facility?->subheadline ?? 'Default Subheading' }}</p>
        <!-- Buttons can go here -->
    </div>
    <h2 class="text-lg font-semibold text-gray-900">Layout Sections</h2>
    <div>
        <ul>
            @foreach($sections as $section)
            <li>
                <input type="hidden" name="sections[]" value="{{ $section['slug'] }}" />
                {{ $section['name'] }}
            </li>
            @endforeach
        </ul>
    </div>
    <h3 class="mt-6 font-semibold">Available Sections</h3>
    <ul>
        @foreach($availableSections as $section)
        <li>
            {{ $section['name'] }}
            <button wire:click="addSection('{{ $section['slug'] }}')" class="ml-2 text-blue-600">Add</button>
        </li>
        @endforeach
    </ul>
</div>