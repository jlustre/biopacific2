<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\PersonalTask;
use App\Models\User;
use Illuminate\Support\Collection;

class AssignedTaskMessageSource implements MemberMessageSource
{
    public function key(): string
    {
        return 'task';
    }

    public function label(): string
    {
        return 'Tasks';
    }

    public function messagesFor(User $user): Collection
    {
        return PersonalTask::query()
            ->where('assigned_to', $user->id)
            ->where('status', PersonalTask::STATUS_PENDING)
            ->with('creator:id,name')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->limit(40)
            ->get()
            ->map(function (PersonalTask $task) {
                $due = $task->due_at
                    ? 'Due '.$task->due_at->timezone(config('app.timezone'))->format('M j, Y')
                    : 'No due date';
                $body = trim((string) preg_replace(
                    '/^\[(?:training_completion_id|upload_verification_id|upload_correction_id):\d+\]\s*/',
                    '',
                    (string) ($task->description ?? '')
                ));
                if ($body === '') {
                    $body = 'A personal task was assigned to you.';
                }

                $isDocumentTask = str_contains((string) ($task->description ?? ''), '[upload_verification_id:')
                    || str_contains((string) ($task->description ?? ''), '[upload_correction_id:')
                    || str_starts_with((string) $task->title, 'Verify document')
                    || str_starts_with((string) $task->title, 'Correct & resubmit');
                $priority = $isDocumentTask ? 'high' : (string) $task->priority;

                return [
                    'id' => 'task:'.$task->id,
                    'source' => $this->key(),
                    'category' => 'Task',
                    'title' => $task->title,
                    'body' => $body.' · '.$due,
                    'tone' => match ($priority) {
                        'high' => 'rose',
                        'low' => 'slate',
                        default => 'amber',
                    },
                    'occurred_at' => $task->updated_at ?? $task->created_at,
                    'route' => filled($task->action_url)
                        ? (string) $task->action_url
                        : route('member.tasks'),
                    'action_label' => filled($task->action_url)
                        ? ((string) ($task->action_label ?: 'Open'))
                        : 'View task',
                    'attention' => true,
                    'meta' => [
                        'priority' => $priority,
                        'from' => $task->creator?->name,
                    ],
                ];
            });
    }
}
