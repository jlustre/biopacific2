<?php

namespace App\Livewire\Concerns;

use App\Support\AssessmentEvaluatorAuthorization;
use App\Support\PreventsSelfAssessment;
use Illuminate\Support\Js;

trait GuardsAgainstSelfAssessment
{
    public bool $evaluatorActionsDisabled = false;

    public function bootGuardsAgainstSelfAssessment(): void
    {
        if (! property_exists($this, 'employeeNum')) {
            return;
        }

        $user = auth()->user();

        $this->evaluatorActionsDisabled = AssessmentEvaluatorAuthorization::isEvaluatorActionBlocked(
            $user,
            $this->employeeNum
        );
    }

    protected function denyEvaluatorAction(?string $message = null): bool
    {
        if (! $this->evaluatorActionsDisabled) {
            return false;
        }

        $message ??= $this->evaluatorActionDeniedMessage();

        $this->js('alert('.Js::from($message).')');

        if (method_exists($this, 'setDraftSaveFeedback')) {
            $this->setDraftSaveFeedback('error', $message);
        } elseif (property_exists($this, 'summaryError')) {
            $this->summaryError = $message;
        }

        return true;
    }

    protected function evaluatorActionDeniedMessage(): string
    {
        if (
            property_exists($this, 'employeeNum')
            && PreventsSelfAssessment::isSelfAssessment(auth()->user(), $this->employeeNum)
        ) {
            return PreventsSelfAssessment::DEFAULT_MESSAGE;
        }

        return AssessmentEvaluatorAuthorization::UNAUTHORIZED_MESSAGE;
    }
}
