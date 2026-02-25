<div class="bg-white rounded-xl shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
    @if(count($recentActivity) > 0)
    <ul class="divide-y divide-gray-200">
        @foreach($recentActivity as $activity)
        <li class="py-2 flex items-center gap-2 text-gray-700">
            <i class="fas {{ $activity['icon'] }} text-{{ $activity['color'] }}-500"></i>
            {{ $activity['message'] }}
        </li>
        @endforeach
    </ul>
    @else
    <p class="text-gray-500 text-center py-4">No recent activity to display.</p>
    @endif

    @if($hasPreEmployment)
    <div class="mt-6 pt-4 border-t border-gray-200">
        @if(!($readOnly ?? false))
        <a href="{{ route('pre-employment.portal') }}"
            class="inline-block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg font-semibold shadow hover:bg-green-700 transition">
            <i class="fas fa-clipboard-check mr-2"></i>Continue Pre-Employment Process
        </a>
        @else
        <div class="text-center text-sm text-gray-500">
            <i class="fas fa-lock mr-2"></i>Pre-employment process is managed by the user
        </div>
        @endif
    </div>
    @endif
</div>