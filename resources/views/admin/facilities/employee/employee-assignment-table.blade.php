<div class="bg-white shadow rounded-lg p-4 mt-8">
    <h2 class="text-lg font-bold mb-4">Job Data History</h2>
    <table class="min-w-full divide-y divide-gray-200 mb-4">
        <thead>
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Date</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Seq</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Facility</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Regular/Temp</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Full/Pt</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hourly Status</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Std Hrs/Wk</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Comp. Rate</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Union Code</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Membership</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']]) as $assign)
            <tr class="border-b">
                <td class="px-3 py-2 text-sm">{{ $assign->effdt }}</td>
                <td class="px-3 py-2 text-sm">{{ $assign->effseq }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->position)->title }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->facility)->name }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->department)->name }}</td>
                <td class="px-3 py-2 text-sm">
                    @if($assign->reg_temp === 'r') Regular @elseif($assign->reg_temp === 't') Temporary @else —
                    @endif
                </td>
                <td class="px-3 py-2 text-sm">
                    @if($assign->full_part_time === 'ft') Full-time @elseif($assign->full_part_time === 'pt')
                    Part-time @elseif($assign->full_part_time === 'pd') Per Diem @else — @endif
                </td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->hourlyStatus)->name ?? '—' }}</td>
                <td class="px-3 py-2 text-sm">{{ $assign->std_hrs_week ?? '—' }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->compensationRate)->name ?? '—' }}</td>
                <td class="px-3 py-2 text-sm">
                    @if($assign->amount !== null && $assign->amount !== '')
                        {{ number_format((float) $assign->amount, 2) }}
                    @else
                        —
                    @endif
                </td>
                <td class="px-3 py-2 text-sm">{{ $employee->union_code ?? '—' }}</td>
                <td class="px-3 py-2 text-sm">
                    @if($employee->effdt_of_membership)
                        {{ $employee->effdt_of_membership instanceof \DateTimeInterface ? $employee->effdt_of_membership->format('Y-m-d') : \Illuminate\Support\Carbon::parse($employee->effdt_of_membership)->format('Y-m-d') }}
                    @else
                        —
                    @endif
                </td>
                <td class="px-3 py-2 text-sm">
                    <a href="#" class="text-blue-600 hover:underline cursor-pointer" @click.prevent="setAssignment({
                            facility_id: '{{ $assign->facility_id }}',
                            dept_id: '{{ $assign->dept_id }}',
                            position_id: '{{ $assign->position_id }}',
                            reports_to: '{{ $assign->reports_to }}',
                            reg_temp: '{{ $assign->reg_temp }}',
                            full_part_time: '{{ $assign->full_part_time }}',
                            hourly_status_id: '{{ $assign->hourly_status_id ?? '' }}',
                            std_hrs_week: '{{ $assign->std_hrs_week ?? '' }}',
                            compensation_rate_id: '{{ $assign->compensation_rate_id ?? '' }}',
                            amount: '{{ $assign->amount ?? '' }}',
                            effdt: '{{ $assign->effdt ? \Illuminate\Support\Carbon::parse($assign->effdt)->format('Y-m-d') : '' }}',
                            effseq: '{{ $assign->effseq }}'
                        })">View/Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>