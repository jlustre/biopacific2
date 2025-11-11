<div x-cloak x-show="mobileOpen" x-transition
    class="fixed top-0 left-0 w-full h-screen z-[9999] bg-black/30 md:hidden overflow-y-auto">
    <div
        class="w-full min-h-screen px-4 py-6 flex flex-col gap-2 bg-white rounded-xl shadow-2xl mt-16 mx-auto max-w-sm">
        <div class="flex justify-between items-center mb-6">
            <span class="font-bold text-xl text-primary ml-2">Menu</span>
            <button @click="mobileOpen = false" class="p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @php
        $activeSections = $activeSections ?? [];
        if (is_string($activeSections)) {
        $activeSections = json_decode($activeSections, true) ?: [];
        } elseif ($activeSections instanceof \Illuminate\Support\Collection) {
        $activeSections = $activeSections->toArray();
        } elseif (!is_array($activeSections)) {
        $activeSections = (array) $activeSections;
        }
        $aboutMenuItems = collect(['about', 'faqs', 'testimonials', 'contact'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        $communityMenuItems = collect(['news', 'gallery', 'blog'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        $careersResourcesMenuItems = collect(['careers', 'resources'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        <nav class="flex flex-col gap-2 mt-2">
            @if(!empty($activeSections) && in_array('book', $activeSections))
            <x-primary-button href="{{ $linkPrefix }}#book" size="lg" :primary="$primary" class="w-full"
                @click="mobileOpen = false">
                Book a Tour
            </x-primary-button>
            @endif
            @if(!empty($activeSections) && in_array('services', $activeSections))
            <button onclick="window.location.href='{{ $linkPrefix }}#services'" @click="mobileOpen = false"
                class="cursor-pointer w-full py-3 px-4 border-2 text-lg font-medium rounded-lg transition-all duration-200"
                style="border-color: {{ $secondary }}; color: {{ $secondary }}; background-color: transparent;"
                @mouseenter="$el.style.backgroundColor = '{{ $secondary }}'; $el.style.color = 'white';"
                @mouseleave="$el.style.backgroundColor = 'transparent'; $el.style.color = '{{ $secondary }}';">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Care & Services
            </button>
            @endif
            @foreach($aboutMenuItems as $section)
            <a href="{{ $linkPrefix }}#{{ $section }}" @click="mobileOpen = false"
                class="cursor-pointer py-3 px-4 rounded text-lg transition-all duration-200 block"
                style="color: {{ $primary }}; background-color: #f1f5f9;"
                @mouseenter="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';"
                @mouseleave="$el.style.backgroundColor = '#f1f5f9'; $el.style.color = '{{ $primary }}';">{{
                $section == 'about' ? 'About Us' :
                ($section == 'contact' ? 'Contact Us' : ucfirst($section))
                }}</a>
            @endforeach
            @foreach($communityMenuItems as $section)
            <a href="{{ $linkPrefix }}#{{ $section }}" @click="mobileOpen = false"
                class="cursor-pointer py-3 px-4 rounded text-lg transition-all duration-200 block"
                style="color: {{ $primary }}; background-color: #f1f5f9;"
                @mouseenter="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';"
                @mouseleave="$el.style.backgroundColor = '#f1f5f9'; $el.style.color = '{{ $primary }}';">{{
                $section == 'news' ? 'News & Events' :
                ($section == 'gallery' ? 'Galleries' :
                ($section == 'blog' ? 'Blogs' : ucfirst($section)))
                }}</a>
            @endforeach
            @foreach($careersResourcesMenuItems as $section)
            <a href="{{ $linkPrefix }}#{{ $section }}" @click="mobileOpen = false"
                class="cursor-pointer py-3 px-4 rounded text-lg transition-all duration-200 block"
                style="color: {{ $primary }}; background-color: #f1f5f9;"
                @mouseenter="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';"
                @mouseleave="$el.style.backgroundColor = '#f1f5f9'; $el.style.color = '{{ $primary }}';">{{
                ucfirst($section) }}</a>
            @endforeach
        </nav>
    </div>
</div>