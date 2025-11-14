<div class="bg-white rounded-xl shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
    <ul class="divide-y divide-gray-200">
        <li class="py-2 flex items-center gap-2 text-gray-700">
            <i class="fas fa-check-circle text-green-500"></i>
            Your account was last updated {{ $lastUpdated ? $lastUpdated->diffForHumans() : 'recently' }}.
        </li>
        <li class="py-2 flex items-center gap-2 text-gray-700">
            <i class="fas fa-hospital text-indigo-500"></i>
            {{ $newFacilitiesCount }} new facilities added this week.
        </li>
        <li class="py-2 flex items-center gap-2 text-gray-700">
            <i class="fas fa-question-circle text-pink-500"></i>
            {{ $newFaqsCount }} new FAQs published.
        </li>
    </ul>
</div>