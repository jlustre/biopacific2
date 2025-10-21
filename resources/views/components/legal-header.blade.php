<div class="bg-white border-b border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-teal-600 mb-4">{{ $legal_title}}</h1>
            <p class="text-lg text-slate-600">{{ $facility['name'] ?? 'Bio-Pacific' }}</p>
            <p class="text-sm text-slate-500 mt-2">Last updated: {{
                \App\Helpers\FacilityDataHelper::getLegalPageUpdatedDate($legal_title) }}</p>
        </div>
    </div>
</div>