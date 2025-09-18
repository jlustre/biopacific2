@php
use App\Support\HipaaWebsiteChecklist;
$items = HipaaWebsiteChecklist::forFacility($facility ?? []);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white/80 backdrop-blur p-6">
    <h3 class="text-xl font-bold text-slate-900">HIPAA Website Readiness</h3>
    <p class="mt-1 text-sm text-slate-600">Quick pass/fail checklist for {{ $facility['name'] ?? 'this facility' }}.</p>

    <ul class="mt-4 divide-y divide-slate-200">
        @foreach($items as $it)
        <li class="py-3 flex items-start gap-3">
            @if($it['passed'])
            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            @else
            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100">
                <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                        d="M12 9v4m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z" />
                </svg>
            </span>
            @endif

            <div class="flex-1">
                <div class="font-medium text-slate-900">{{ $it['label'] }}</div>
                @if(!$it['passed'])
                <div class="text-sm text-slate-600">{{ $it['help'] }}</div>
                @endif
            </div>

            <div>
                <span class="inline-flex items-center rounded-full px-2 py-1 text-[11px] font-medium ring-1"
                    @class([ 'text-emerald-700 bg-emerald-50 ring-emerald-200'=> $it['passed'],
                    'text-amber-700 bg-amber-50 ring-amber-200' => !$it['passed'],
                    ])>
                    {{ $it['passed'] ? 'OK' : 'Action' }}
                </span>
            </div>
        </li>
        @endforeach
    </ul>

    <div class="mt-4 text-xs text-slate-500">
        *This is an internal readiness checklist (not legal advice). Keep BAAs and policies on file.
    </div>
</div>