@if(count($myTasks) === 0)
<p class="px-4 py-6 text-center text-sm text-slate-500">You’re caught up. Check back when HR assigns new items.</p>
@else
<ul class="divide-y divide-slate-100">
    @foreach($myTasks as $task)
    <li class="flex items-start gap-3 px-4 py-2.5 text-sm">
        <span class="mt-0.5 text-teal-600"><i class="fa-regular fa-circle"></i></span>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-slate-900">{{ $task['title'] ?? 'Task' }}</p>
            @if(!empty($task['description']))
            <p class="text-xs text-slate-500">{{ $task['description'] }}</p>
            @endif
        </div>
        @if(!empty($task['route']) || ($task['action'] ?? '') === 'submit')
        @if(($task['action'] ?? '') === 'submit')
        <form method="POST" action="{{ route('settings.profile.submit-hr-review') }}" class="shrink-0">
            @csrf
            <button type="submit" class="text-xs font-bold text-teal-700 hover:text-teal-900">Submit</button>
        </form>
        @elseif(!empty($task['route']))
        <a href="{{ $task['route'] }}" class="shrink-0 text-xs font-bold text-teal-700 hover:text-teal-900">
            @php
                $actionLabel = match ($task['action'] ?? '') {
                    'upload' => 'Upload',
                    'sign' => 'Sign',
                    default => 'Open',
                };
            @endphp
            {{ $actionLabel }}
        </a>
        @endif
        @endif
    </li>
    @endforeach
</ul>
@endif
