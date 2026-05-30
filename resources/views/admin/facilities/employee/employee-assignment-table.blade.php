@php
    $isSelfService = $isSelfService ?? false;
    $canManageJobData = $canManageJobData ?? true;
    $canShowJobDataEditLink = $canManageJobData && ! $isSelfService;
@endphp
<div class="bg-white shadow rounded-lg p-4 mt-8">
    <h2 class="text-lg font-bold mb-2">Job Data History</h2>
    @if($isSelfService)
        <p class="text-sm text-gray-600 mb-4">
            Past job data records are shown for reference only. Job assignment changes are managed by HR—contact your facility administrator if anything looks incorrect.
        </p>
    @endif
    <div class="overflow-x-auto overscroll-x-contain -mx-1 px-1 sm:mx-0 sm:px-0" style="-webkit-overflow-scrolling: touch;">
        <table class="min-w-max w-full divide-y divide-gray-200 mb-4 text-sm">
            <thead>
                <tr>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Date</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Seq</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Facility</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Regular/Temp</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Full/Pt</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hourly Status</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Std Hrs/Wk</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Comp. Rate</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Union Code</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Membership</th>
                    <th class="whitespace-nowrap px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']]) as $assign)
                <tr class="border-b">
                    <td class="whitespace-nowrap px-3 py-2">{{ $assign->effdt }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ $assign->effseq }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ optional($assign->position)->title }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ optional($assign->facility)->name }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ optional($assign->department)->name }}</td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @if($assign->reg_temp === 'r') Regular @elseif($assign->reg_temp === 't') Temporary @else —
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @if($assign->full_part_time === 'ft') Full-time @elseif($assign->full_part_time === 'pt')
                        Part-time @elseif($assign->full_part_time === 'pd') Per Diem @else — @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">{{ optional($assign->hourlyStatus)->name ?? '—' }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ $assign->std_hrs_week ?? '—' }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ optional($assign->compensationRate)->name ?? '—' }}</td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @if($assign->amount !== null && $assign->amount !== '')
                            {{ number_format((float) $assign->amount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">{{ $employee->union_code ?? '—' }}</td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @if($employee->effdt_of_membership)
                            {{ $employee->effdt_of_membership instanceof \DateTimeInterface ? $employee->effdt_of_membership->format('Y-m-d') : \Illuminate\Support\Carbon::parse($employee->effdt_of_membership)->format('Y-m-d') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @if($canShowJobDataEditLink)
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
                        @else
                        <span class="text-gray-400 italic text-xs">HR only</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>