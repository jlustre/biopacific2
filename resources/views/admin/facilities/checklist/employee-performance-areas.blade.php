<div class="mb-4">
    <h3 class="font-bold mb-2">PERFORMANCE AREAS</h3>
    <div class="italic text-xs text-gray-600 mb-2">
        Assess the employee’s knowledge, skills, and abilities, as outlined below. For each area, you can verify,
        revoke, or view details. Ratings and comments are editable and saved via AJAX.
    </div>
    @php
    use App\Models\EmployeePerformanceItem;
    $partFSections = EmployeePerformanceItem::orderBy('order')->get()->groupBy('section');
    @endphp
    @foreach ($partFSections as $sectionLabel => $items)
    <table class="min-w-full border text-xs md:text-sm mb-2">
        <thead>
            <tr class="bg-gray-100">
                <th colspan="2" class="border px-2 py-1 text-left"><em>{{ $sectionLabel }}</em></th>
                <th class="border px-2 py-1 text-center">RATING</th>
                <th class="border px-2 py-1 text-center">ASSESSED DATE</th>
                <th class="border px-2 py-1 text-center">ASSESSED BY</th>
                <th class="border px-2 py-1 text-center">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $itemIdx => $item)
            @php
            $itemKey = 'F_' . md5($sectionLabel . '_' . $item->id);
            $empChecklist = $empPerformanceChecklist[$itemKey] ?? null;
            @endphp
            <tr>
                <td class="border px-2 py-1">{{ $item->label ?? '' }}</td>
                <td class="border px-2 py-1">{!! $item->item !!}</td>
                <td class="border px-2 py-1 text-center">
                    @php
                    $ratingText = '';
                    if (isset($empChecklist['rating'])) {
                    if ($empChecklist['rating'] == 1) $ratingText = 'Below';
                    elseif ($empChecklist['rating'] == 2) $ratingText = 'Meets';
                    elseif ($empChecklist['rating'] == 3) $ratingText = 'Exceeds';
                    }
                    @endphp
                    {{ $ratingText }}
                </td>
                <td class="border px-2 py-1 text-center">
                    {{ $empChecklist['verified_dt'] ?? '' }}
                </td>
                <td class="border px-2 py-1 text-center">
                    @if(isset($users) && !empty($empChecklist['verified_by']))
                    {{ optional($users->firstWhere('id', $empChecklist['verified_by']))->name ??
                    $empChecklist['verified_by'] }}
                    @endif
                </td>
                <td class="border px-2 py-1 text-center">
                    @if(!empty($empChecklist['verified_by']))
                    <a href="#" class="text-red-600 underline mr-1 unverify-link" title="Revoke Assessment"
                        data-item-key="{{ $itemKey }}" data-emp-id="{{ $employee->emp_id }}">Revoke</a>
                    <span>|</span>
                    <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Assessment Details"
                        data-item-key="{{ $itemKey }}" data-emp-id="{{ $employee->emp_id }}">View</a>
                    @else
                    <a href="#" class="text-teal-600 underline verify-link" title="Assess Item"
                        data-item-key="{{ $itemKey }}" data-emp-id="{{ $employee->emp_id }}">Assess</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
            class="border rounded w-full min-h-[40px] mt-1" rows="2">{{ $empChecklist['comments'] ?? '' }}</textarea>
    </div>
    @endforeach
    <!-- Totals and overall rating can be dynamically calculated and displayed here as needed -->
</div>