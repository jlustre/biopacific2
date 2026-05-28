@props([
    'title' => 'COMPETENCY EVALUATION SUMMARY',
    'reviewerName' => 'Super Admin',
    'employeeName' => 'Rodriguez, June',
    'employeeTitle' => 'Registered Nurse',
    'totalPoints' => 0,
    'average' => '0.00',
    'overallRating' => '',
])
<div class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4">
    <div class="font-bold text-lg text-gray-800 mb-1">{{ $title }}</div>
    <div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>
    <div class="mb-3">
        <div class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800">
            @include('admin.facilities.checklist.partials.part-g-average-legend')
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
        <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
            <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL POINTS</div>
            <div class="text-2xl font-bold text-gray-700">{{ $totalPoints }}</div>
        </div>
        <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
            <div class="text-xs font-semibold text-gray-500 mb-1">AVERAGE</div>
            <div class="text-2xl font-bold text-gray-700">{{ $average }}</div>
        </div>
        <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
            <div class="text-xs font-semibold text-gray-500 mb-1">OVERALL RATING</div>
            <div class="text-2xl font-bold text-gray-700">{{ $overallRating }}</div>
        </div>
    </div>
    <div class="mb-3">
        <label class="block text-xs font-semibold text-gray-700 mb-1">COMMENTS</label>
        <textarea class="w-full rounded border border-gray-300 bg-slate-100 p-3 text-gray-700 min-h-[100px] resize-y" placeholder="Enter comments here..."></textarea>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER NAME/SIGNATURE</label>
                <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerName }}" readonly />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER TITLE</label>
                <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEW DATE</label>
                <input type="date" class="w-full rounded border border-gray-300 bg-white p-2" />
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE NAME/SIGNATURE</label>
                <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeName }}" readonly />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE TITLE</label>
                <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeTitle }}" readonly />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE DATE</label>
                <input type="date" class="w-full rounded border border-gray-300 bg-white p-2" />
            </div>
        </div>
    </div>
    <div class="flex flex-col md:flex-row justify-end gap-2 mt-2">
        <button type="button" class="rounded border border-gray-400 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-100">Save as Draft</button>
        <button type="submit" class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800">Submit Assessment</button>
    </div>
</div>
