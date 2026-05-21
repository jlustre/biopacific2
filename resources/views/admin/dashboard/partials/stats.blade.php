<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Facilities</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ $facilities->count() }}</p>
            </div>
            <span class="rounded-2xl bg-teal-50 p-3 text-teal-700"><i class="fa-solid fa-building"></i></span>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Active Facilities</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ $facilities->where('is_active', true)->count() }}</p>
            </div>
            <span class="rounded-2xl bg-emerald-50 p-3 text-emerald-700"><i class="fa-solid fa-circle-check"></i></span>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">States Covered</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ $facilitiesByState->count() }}</p>
            </div>
            <span class="rounded-2xl bg-amber-50 p-3 text-amber-700"><i class="fa-solid fa-map-pin"></i></span>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Unique Domains</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ $facilities->pluck('domain')->unique()->filter()->count() }}</p>
            </div>
            <span class="rounded-2xl bg-sky-50 p-3 text-sky-700"><i class="fa-solid fa-globe"></i></span>
        </div>
    </div>
</div>
