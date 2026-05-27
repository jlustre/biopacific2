@php
    $fedStatusLabels = ['1' => 'Single', '2' => 'Married'];
    $residentLabels = ['Y' => 'Resident', 'N' => 'Non-Resident'];
@endphp
<div class="bg-white shadow rounded-lg p-4 mt-8">
    <h2 class="text-lg font-bold mb-4">Tax Data History</h2>
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
                <tr class="border-b">
                    <td class="px-3 py-2">{{ $tax->effdt?->format('Y-m-d') ?? $tax->effdt }}</td>
                    <td class="px-3 py-2">{{ $tax->effseq }}</td>
                    <td class="px-3 py-2">{{ $fedStatusLabels[(string) $tax->fed_tax_data] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $fedStatusLabels[(string) $tax->state_tax_data] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $residentLabels[strtoupper((string) $tax->resident)] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $tax->resident_state ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $tax->locality ?? '—' }}</td>
                    <td class="px-3 py-2">
                        <a href="#" class="text-blue-600 hover:underline cursor-pointer" @click.prevent="setTax({
                            effdt: @json($tax->effdt ? \Illuminate\Support\Carbon::parse($tax->effdt)->format('Y-m-d') : ''),
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
