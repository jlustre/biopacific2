<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AssessmentEvaluatorAuthorization
{
    public const UNAUTHORIZED_MESSAGE = 'You are not authorized to review assessments. Only supervisors, DSD, and facility administrators may complete competency and performance reviews.';

    /**
     * Portal roles documented as Part F / Part G reviewers.
     *
     * @return list<string>
     */
    public static function evaluatorPortalRoles(): array
    {
        return [
            User::superAdminRoleName(),
            'admin',
            'rdhr',
            'facility-admin',
            'facility-dsd',
        ];
    }

    public static function canEvaluate(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($actor->hasRole(self::evaluatorPortalRoles())) {
            return true;
        }

        return self::hasSupervisorPosition($actor);
    }

    public static function hasSupervisorPosition(?User $actor): bool
    {
        $employee = $actor?->resolvedBpEmployee(['currentAssignment.position']);

        return (bool) ($employee?->currentAssignment?->position?->supervisor_role ?? false);
    }

    public static function isEvaluatorActionBlocked(?User $actor, BPEmployee|string $target): bool
    {
        if (! self::canEvaluate($actor)) {
            return true;
        }

        return PreventsSelfAssessment::isSelfAssessment($actor, $target);
    }

    public static function assertCanEvaluateReviewer(?User $actor, BPEmployee|string $target): void
    {
        PreventsSelfAssessment::assertNotSelf($actor, $target);

        if (! self::canEvaluate($actor)) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }
    }

    public static function jsonDenyIfUnauthorizedEvaluator(?User $actor, BPEmployee|string $target): ?JsonResponse
    {
        if ($response = PreventsSelfAssessment::jsonDenyIfSelf($actor, $target)) {
            return $response;
        }

        if (! self::canEvaluate($actor)) {
            return response()->json([
                'success' => false,
                'message' => self::UNAUTHORIZED_MESSAGE,
            ], 403);
        }

        return null;
    }
}
