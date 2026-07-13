@php
    /** @var \App\Models\EmployeeTrainingItem $item */
    /** @var \App\Models\BPEmployee $employee */
    /** @var \App\Models\EmployeeTrainingCompletion|null $completion */
@endphp
<tr class="{{ ! empty($rowEven) ? 'bg-teal-50' : 'bg-white' }} align-top">
    <td class="px-3 py-2">
        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
        <div class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $frequencyLabel ?? $item->frequencyShortLabel() }}</div>
        @if($item->description)
        <div class="text-xs text-slate-500">{{ $item->description }}</div>
        @endif
        @if($item->provider_label)
        <div class="mt-1 text-[11px] font-medium uppercase tracking-wide text-teal-800">{{ $item->provider_label }}</div>
        @endif
        @if(! empty($satisfiedFromPrior))
        <div class="mt-2 rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs text-emerald-900">
            Not due this cycle — prior completion still covers the frequency interval.
        </div>
        @endif
        @if($completion?->rejection_reason)
        <div class="mt-2 rounded border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-900">
            <span class="font-semibold">Returned:</span> {{ $completion->rejection_reason }}
        </div>
        @endif
        @if($completion?->notes)
        <div class="mt-1 text-xs text-slate-600"><span class="font-medium">Notes:</span> {{ $completion->notes }}</div>
        @endif
    </td>
    <td class="px-3 py-2">
        @if($moduleUrl)
        <a href="{{ $moduleUrl }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex rounded-md bg-sky-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-sky-700">Open</a>
        @else
        <span class="text-xs text-slate-400">—</span>
        @endif
    </td>
    <td class="px-3 py-2">
        <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">{{ $label }}</span>
    </td>
    <td class="px-3 py-2">
        <div class="flex flex-col gap-2">
            @if($canStart)
            <form method="POST" action="{{ route('admin.employees.training-completions.start', ['employee' => $employee->id, 'trainingItem' => $item->id]) }}">
                @csrf
                @if($periodId)
                <input type="hidden" name="assessment_period_id" value="{{ $periodId }}">
                @endif
                <button type="submit" class="rounded-md bg-sky-700 px-2.5 py-1 text-xs font-semibold text-white hover:bg-sky-800">Start</button>
            </form>
            @endif

            @if($canSubmit)
            <form method="POST" action="{{ route('admin.employees.training-completions.submit', ['employee' => $employee->id, 'trainingItem' => $item->id]) }}" class="space-y-1">
                @csrf
                @if($periodId)
                <input type="hidden" name="assessment_period_id" value="{{ $periodId }}">
                @endif
                <input type="text" name="notes" maxlength="1000" placeholder="Optional note"
                       class="w-full rounded border-teal-300 text-xs">
                <button type="submit" class="rounded-md bg-amber-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-700">Submit for completion</button>
            </form>
            @endif

            @if($canDecide)
            <div class="flex flex-wrap items-start gap-2">
                <form method="POST" action="{{ route('admin.employees.training-completions.approve', ['employee' => $employee->id, 'trainingItem' => $item->id]) }}">
                    @csrf
                    @if($periodId)
                    <input type="hidden" name="assessment_period_id" value="{{ $periodId }}">
                    @endif
                    <button type="submit" class="rounded-md bg-emerald-700 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-800">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.employees.training-completions.reject', ['employee' => $employee->id, 'trainingItem' => $item->id]) }}" class="min-w-[12rem] flex-1 space-y-1">
                    @csrf
                    @if($periodId)
                    <input type="hidden" name="assessment_period_id" value="{{ $periodId }}">
                    @endif
                    <input type="text" name="rejection_reason" required maxlength="2000" placeholder="Reason to return"
                           class="w-full rounded border-rose-300 text-xs">
                    <button type="submit" class="rounded-md bg-rose-700 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-800">Reject / return</button>
                </form>
            </div>
            @endif

            @if(! $canStart && ! $canSubmit && ! $canDecide)
                @if(! empty($actionsLocked))
                <span class="text-xs text-slate-400">Select a period first</span>
                @elseif(! empty($satisfiedFromPrior))
                <span class="text-xs text-emerald-800">Satisfied for this interval</span>
                @elseif($completion?->status === \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED)
                <span class="text-xs text-emerald-800">Approved complete</span>
                @elseif($completion?->status === \App\Models\EmployeeTrainingCompletion::STATUS_SUBMITTED)
                <span class="text-xs text-amber-800">Awaiting DSD / supervisor</span>
                @else
                <span class="text-xs text-slate-400">—</span>
                @endif
            @endif
        </div>
    </td>
    <td class="px-3 py-2 text-xs text-slate-600">
        @if($completion?->started_at)
        <div>Started {{ $completion->started_at->format('Y-m-d') }}</div>
        @endif
        @if($completion?->submitted_at)
        <div>Submitted {{ $completion->submitted_at->format('Y-m-d') }}</div>
        @endif
        @if($completion?->reviewed_at)
        <div>
            Reviewed {{ $completion->reviewed_at->format('Y-m-d') }}
            <div class="text-slate-500">{{ $completion->reviewedByUser?->name ?? '—' }}</div>
        </div>
        @endif
        @if($completion?->completed_at && $completion->status === \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED)
        <div class="mt-1 font-medium text-emerald-800">
            Completed {{ $completion->completed_at->format('Y-m-d') }}
            <div class="font-normal text-slate-500">{{ $completion->completedByUser?->name ?? '—' }}</div>
        </div>
        @endif
        @if(! $completion?->started_at && ! $completion?->submitted_at && ! $completion?->completed_at)
        —
        @endif
    </td>
</tr>
