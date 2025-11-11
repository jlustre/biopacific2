<div class="flex items-center justify-start min-w-0">
    <span class="inline-flex items-center justify-center bg-primary/10 text-primary font-bold mr-2 ml-0 flex-shrink-0">
        <a href="{{ $linkPrefix }}#top" class="flex items-center gap-1">
            <img src="{{ asset('images/bplogo.png') }}" alt="Logo"
                class="h-8 w-8 sm:h-10 sm:w-10 md:h-12 md:w-12 lg:h-16 lg:w-16 object-contain">
        </a>
    </span>
    <a href="{{ $linkPrefix }}#top" class="flex items-center gap-1 min-w-0 max-w-[70vw]">
        <div class="flex flex-col min-w-0 max-w-full">
            <div
                class="font-semibold text-sm sm:text-md md:text-lg lg:text-xl leading-tight truncate max-w-[280px] md:max-w-[150px] lg:max-w-md">
                {{ $facility['name'] }}</div>
            @if(!empty($facility['tagline']))
            <div
                class="block text-xs lg:text-sm leading-tight text-slate-500 dark:text-slate-400 truncate max-w-[60vw] xs:max-w-[200px] sm:max-w-xs">
                {{ $facility['tagline'] }}</div>
            @endif
        </div>
    </a>
</div>