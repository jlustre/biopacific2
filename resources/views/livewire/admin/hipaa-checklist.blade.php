<div class="rounded-2xl border border-slate-200 bg-white/80 backdrop-blur p-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-900">HIPAA Website Readiness</h3>
            <p class="text-sm text-slate-600">Internal checklist for {{ $facility->name }}.</p>
        </div>
        <div class="text-sm">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full ring-1 ring-slate-200 bg-slate-50">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>
                    {{ collect($rows)->where('passed', true)->count() }} / {{ count($rows) }} Passed
                </span>
            </span>
        </div>
    </div>

    <ul class="mt-4 divide-y divide-slate-200">
        @foreach($rows as $row)
        <li class="py-3 grid gap-3 md:grid-cols-[1fr,auto] md:items-center">
            <div class="flex items-start gap-3">
                @if($row['passed'])
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

                <div>
                    <div class="font-medium text-slate-900">{{ $row['label'] }}</div>
                    @if(!$row['passed'])
                    <div class="text-sm text-slate-600">{{ $row['help'] }}</div>
                    @endif
                </div>
            </div>

            {{-- Toggle bound to flags --}}
            @php $key = $row['key']; @endphp
            <label class="inline-flex items-center gap-2 justify-self-end cursor-pointer select-none">
                <input type="checkbox" class="sr-only" wire:model.lazy="flags.{{ $key }}">
                <span class="text-xs text-slate-600">Mark as done</span>
                <span class="relative inline-flex h-6 w-10 items-center rounded-full transition"
                    style="background: {{ ($flags[$key] ?? false) ? '#10B981' : '#e5e7eb' }}">
                    <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition"
                        style="transform: translateX({{ ($flags[$key] ?? false) ? '16px' : '0px' }});"></span>
                </span>
            </label>
        </li>
        @endforeach
    </ul>

    {{-- Optional: inline NPP URL editor --}}
    <div class="mt-5 grid gap-3 md:grid-cols-[1fr,auto] md:items-center">
        <div>
            <label class="block text-sm font-medium text-slate-700">NPP URL (public)</label>
            <input type="url" class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-400 focus:ring-0"
                placeholder="https://example.com/privacy-practices" wire:change="$refresh"
                value="{{ old('npp_url', $facility->npp_url) }}" onblur="window.livewireFindFacilityNpp(this.value)">
            <p class="mt-1 text-xs text-slate-500">Used by the “NPP page” check above.</p>
        </div>
        <button type="button" class="justify-self-end rounded-full px-4 py-2 text-white font-semibold"
            style="background: {{ $facility->primary_color ?? '#0EA5E9' }}" onclick="window.livewireSaveNpp()">
            Save NPP URL
        </button>
    </div>

    <script>
        // Simple bridge to update NPP without full form
    window.livewire_npp_url = @json($facility->npp_url);
    window.livewireFindFacilityNpp = function(url){ window.livewire_npp_url = url; };
    window.livewireSaveNpp = function(){
      window.dispatchEvent(new CustomEvent('save-npp-url'));
    };
    </script>
</div>

{{-- Listen and persist NPP URL via Livewire --}}
@push('scripts')
<script>
    document.addEventListener('save-npp-url', () => {
    Livewire.find(@this.__instance.id).call('saveNpp', window.livewire_npp_url);
  });
</script>
@endpush