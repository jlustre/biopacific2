@props([
'backUrl',
'backLabel' => 'Back',
'xData' => "{ activeSection: 'personal' }",
])

<div class="container mx-auto py-8 px-4">
    <div class="mb-6">
        <a href="{{ $backUrl }}" class="text-teal-600 hover:text-teal-700 font-semibold flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> {{ $backLabel }}
        </a>
    </div>

    {{ $header }}

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="{{ $xData }}">
        <aside class="bg-white rounded-lg shadow-md p-6 lg:col-span-1 h-fit">
            {{ $sidebar }}
        </aside>

        <section class="lg:col-span-3 space-y-6">
            {{ $slot }}
        </section>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
        {{ $actions }}
    </div>
</div>