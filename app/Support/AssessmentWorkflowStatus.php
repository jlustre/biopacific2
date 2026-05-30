<?php

namespace App\Support;

class AssessmentWorkflowStatus
{
    public const DRAFT = 'draft';

    public const FOR_EMPLOYEE_CONFIRMATION = 'for_employee_confirmation';

    public const FOR_REVIEWER_APPROVAL = 'for_reviewer_approval';

    public const COMPLETED = 'completed';

    /** @var list<string> */
    public const ALL = [
        self::DRAFT,
        self::FOR_EMPLOYEE_CONFIRMATION,
        self::FOR_REVIEWER_APPROVAL,
        self::COMPLETED,
    ];

    public static function normalize(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        return match ($status) {
            'for_employee_signature' => self::FOR_EMPLOYEE_CONFIRMATION,
            'for_reviewer_signature' => self::FOR_REVIEWER_APPROVAL,
            'in_progress', '' => self::DRAFT,
            default => $status,
        };
    }

    public static function label(?string $status): string
    {
        return match (self::normalize($status)) {
            self::FOR_EMPLOYEE_CONFIRMATION => 'For Employee confirmation',
            self::FOR_REVIEWER_APPROVAL => 'For Reviewer approval',
            self::COMPLETED => 'Completed',
            self::DRAFT => 'In Progress',
            default => ucwords(str_replace('_', ' ', self::normalize($status))),
        };
    }

    public static function isCompleted(?string $status): bool
    {
        return self::normalize($status) === self::COMPLETED;
    }

    public static function isLocked(?string $status): bool
    {
        return self::isCompleted($status);
    }

    public static function employeeCanConfirm(?string $status): bool
    {
        return self::normalize($status) === self::FOR_EMPLOYEE_CONFIRMATION;
    }

    public static function reviewerCanApprove(?string $status): bool
    {
        return self::normalize($status) === self::FOR_REVIEWER_APPROVAL;
    }

    public static function reviewerCanEdit(?string $status): bool
    {
        return in_array(self::normalize($status), [self::DRAFT, self::FOR_REVIEWER_APPROVAL], true);
    }

    public static function employeeCanSendBack(?string $status): bool
    {
        return in_array(self::normalize($status), [self::FOR_EMPLOYEE_CONFIRMATION, self::COMPLETED], true);
    }

    public static function reviewerCanReopen(?string $status): bool
    {
        return self::isCompleted($status);
    }

    /**
     * @return array{status: string, status_label: string, is_completed: bool, can_edit: bool}
     */
    public static function meta(?string $status): array
    {
        $normalized = self::normalize($status);

        return [
            'status' => $normalized,
            'status_label' => self::label($normalized),
            'is_completed' => self::isCompleted($normalized),
            'can_edit' => ! self::isCompleted($normalized),
        ];
    }
}
