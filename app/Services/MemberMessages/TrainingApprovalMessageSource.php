<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\EmployeeTrainingCompletion;
use App\Models\User;
use Illuminate\Support\Collection;

class TrainingApprovalMessageSource implements MemberMessageSource
{
    public function key(): string
    {
        return 'training';
    }

    public function label(): string
    {
        return 'Trainings';
    }

    public function messagesFor(User $user): Collection
    {
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee()
            : null;

        if (! $employee) {
            return collect();
        }

        return EmployeeTrainingCompletion::query()
            ->where('employee_num', $employee->employee_num)
            ->where('status', EmployeeTrainingCompletion::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(90))
            ->with(['trainingItem:id,name,frequency', 'reviewedByUser:id,name'])
            ->latest('completed_at')
            ->limit(25)
            ->get()
            ->map(function (EmployeeTrainingCompletion $completion) {
                $trainingName = $completion->trainingItem?->name ?? 'Training';
                $reviewer = $completion->reviewedByUser?->name;
                $body = $reviewer
                    ? "Approved by {$reviewer}. Your training is marked complete."
                    : 'Your training was approved and marked complete.';

                $route = route('member.checklists').'?'.http_build_query(array_filter([
                    'assessment_period_id' => $completion->assessment_period_id,
                ]));

                $isRecent = $completion->completed_at
                    && $completion->completed_at->greaterThanOrEqualTo(now()->subDays(14));

                return [
                    'id' => 'training:'.$completion->id,
                    'source' => $this->key(),
                    'category' => 'Training',
                    'title' => 'Training completed: '.$trainingName,
                    'body' => $body,
                    'tone' => 'brand',
                    'occurred_at' => $completion->completed_at ?? $completion->updated_at,
                    'route' => $route,
                    'action_label' => 'View checklist',
                    'attention' => (bool) $isRecent,
                    'meta' => [
                        'training_item_id' => $completion->employee_training_item_id,
                        'assessment_period_id' => $completion->assessment_period_id,
                    ],
                ];
            });
    }
}
