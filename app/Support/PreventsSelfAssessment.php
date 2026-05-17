<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PreventsSelfAssessment
{
    public const DEFAULT_MESSAGE = 'You cannot perform this assessment on yourself. Another supervisor or evaluator must complete it.';

    /**
     * Whether the authenticated user is the same person as the target employee.
     *
     * Detection uses {@see User::resolvedBpEmployee()} (user_id when present, else email)
     * and compares {@see BPEmployee::employee_num} values.
     */
    public static function isSelfAssessment(?User $actor, BPEmployee|string $target): bool
    {
        if (! $actor) {
            return false;
        }

        $targetEmployeeNum = $target instanceof BPEmployee
            ? (string) $target->employee_num
            : (string) $target;

        if ($targetEmployeeNum === '') {
            return false;
        }

        $actorEmployee = $actor->resolvedBpEmployee();

        return $actorEmployee !== null
            && (string) $actorEmployee->employee_num === $targetEmployeeNum;
    }

    public static function assertNotSelf(?User $actor, BPEmployee|string $target, ?string $message = null): void
    {
        if (self::isSelfAssessment($actor, $target)) {
            abort(403, $message ?? self::DEFAULT_MESSAGE);
        }
    }

    public static function jsonDenyIfSelf(?User $actor, BPEmployee|string $target, ?string $message = null): ?JsonResponse
    {
        if (! self::isSelfAssessment($actor, $target)) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => $message ?? self::DEFAULT_MESSAGE,
        ], 403);
    }
}
