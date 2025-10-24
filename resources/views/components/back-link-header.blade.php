<!-- Header -->
<div class="bg-white shadow-sm border-b border-teal-600">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.facilities.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $title_hdr }}</h1>
                    <p class="text-gray-600">{{ $subtitle_hdr }}</p>
                </div>
            </div>
            @if (@isset($preview) && $preview)
            <div class="flex gap-3">
                <a href="{{ route('admin.dashboard.facility', $facility->id) }}"
                    class="bg-teal-100 text-gray-700 px-2 py-2 rounded-lg hover:bg-teal-200 transition-colors text-sm font-medium">
                    Preview Site
                </a>
            </div>
            @endif
        </div>
    </div>
</div>