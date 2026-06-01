@php
    $isSelfService = $isSelfService ?? false;
    $taxLatestEffdt = $taxLatestEffdt ?? null;
    $taxLatestEffseq = $taxLatestEffseq ?? null;
    $canEditCoreTabs = $canEditCoreTabs ?? true;
    $fedStatusLabels = ['1' => 'Single', '2' => 'Married'];
    $residentLabels = ['Y' => 'Resident', 'N' => 'Non-Resident'];
@endphp
<div class="bg-white shadow rounded-lg p-4 mt-8">
    <h2 class="text-lg font-bold mb-2">Tax Data History</h2>
    @if($isSelfService)
        <p class="text-sm text-gray-600 mb-4">
            Past tax data records are shown for reference only. If a historical record needs to be corrected or removed, contact your DSD, facility administrator, or RDHR.
        </p>
    @endif
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 mb-4 text-sm">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Date</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Seq</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Federal</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">State</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Res. State</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Locality</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($employee->taxData ?? collect())->sortBy([['effdt', 'desc'], ['effseq', 'desc']]) as $tax)
                @php
                    $taxEffdt = $tax->effdt?->format('Y-m-d') ?? (string) $tax->effdt;
                    $isLatestTax = $taxLatestEffdt !== null
                        && $taxLatestEffseq !== null
                        && (string) $taxEffdt === (string) $taxLatestEffdt
                        && (string) $tax->effseq === (string) $taxLatestEffseq;
                    $canEmployeeEditTax = $canEditCoreTabs && (! $isSelfService || $isLatestTax);
                @endphp
                <tr class="border-b">
                    <td class="px-3 py-2">{{ $taxEffdt }}</td>
                    <td class="px-3 py-2">{{ $tax->effseq }}</td>
                    <td class="px-3 py-2">{{ $fedStatusLabels[(string) $tax->fed_tax_data] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $fedStatusLabels[(string) $tax->state_tax_data] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $residentLabels[strtoupper((string) $tax->resident)] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $tax->resident_state ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $tax->locality ?? '—' }}</td>
                    <td class="px-3 py-2">
                        @if($canEmployeeEditTax)
                        <a href="#" class="text-blue-600 hover:underline cursor-pointer" @click.prevent="setTax({
                            effdt: @json($taxEffdt),
                            effseq: @json($tax->effseq),
                            fed_tax_data: @json($tax->fed_tax_data ?? ''),
                            fed_withholding_allowance: @json($tax->fed_withholding_allowance ?? ''),
                            state_tax_data: @json($tax->state_tax_data ?? ''),
                            state_withholding_allowance1: @json($tax->state_withholding_allowance1 ?? ''),
                            resident: @json(strtoupper((string) $tax->resident)),
                            local_withholding_allowance: @json($tax->local_withholding_allowance ?? ''),
                            locality: @json($tax->locality ?? ''),
                            county: @json($tax->county ?? ''),
                            addl_withholding_percentage1: @json($tax->addl_withholding_percentage1 ?? ''),
                            addl_withholding_amount1: @json($tax->addl_withholding_amount1 ?? ''),
                            addl_withholding_percentage2: @json($tax->addl_withholding_percentage2 ?? ''),
                            addl_withholding_amount2: @json($tax->addl_withholding_amount2 ?? ''),
                            resident_state: @json($tax->resident_state ?? 'CA')
                        })">View/Edit</a>
                        @else
                        <span class="text-gray-400 italic text-xs">HR only</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-gray-500 py-4">No tax data records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
