<div class="bg-white shadow rounded-lg p-4 mt-8">
    <h2 class="text-lg font-bold mb-4">Assignment History</h2>
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
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']]) as $assign)
            <tr class="border-b">
                <td class="px-3 py-2 text-sm">{{ $assign->effdt }}</td>
                <td class="px-3 py-2 text-sm">{{ $assign->effseq }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->position)->position_title }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->facility)->name }}</td>
                <td class="px-3 py-2 text-sm">{{ optional($assign->department)->dept_name }}</td>
                <td class="px-3 py-2 text-sm">
                    @if($assign->reg_temp === 'r') Regular @elseif($assign->reg_temp === 't') Temporary @else —
                    @endif
                </td>
                <td class="px-3 py-2 text-sm">
                    @if($assign->full_part_time === 'ft') Full-time @elseif($assign->full_part_time === 'pt')
                    Part-time @elseif($assign->full_part_time === 'pd') Per Diem @else — @endif
                </td>
                <td class="px-3 py-2 text-sm">
                    <a href="#" class="text-blue-600 hover:underline cursor-pointer" @click.prevent="setAssignment({
                            facility_id: '{{ $assign->facility_id }}',
                            dept_id: '{{ $assign->dept_id }}',
                            job_code_id: '{{ $assign->job_code_id }}',
                            reports_to_emp_id: '{{ $assign->reports_to_emp_id }}',
                            reg_temp: '{{ $assign->reg_temp }}',
                            full_part_time: '{{ $assign->full_part_time }}',
                            bargaining_unit_id: '{{ $assign->bargaining_unit_id ?? '' }}',
                            seniority_date: '{{ $assign->seniority_date }}',
                            effdt: '{{ $assign->effdt }}',
                            effseq: '{{ $assign->effseq }}'
                        })">View/Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>