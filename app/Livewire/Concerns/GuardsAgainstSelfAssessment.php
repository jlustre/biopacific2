<?php

namespace App\Livewire\Concerns;

use App\Support\PreventsSelfAssessment;

trait GuardsAgainstSelfAssessment
{
    public bool $evaluatorActionsDisabled = false;

    public function bootGuardsAgainstSelfAssessment(): void
    {
        if (! property_exists($this, 'employeeNum')) {
            return;
        }

        $this->evaluatorActionsDisabled = PreventsSelfAssessment::isSelfAssessment(
            auth()->user(),
            $this->employeeNum
        );
    }

    protected function denyEvaluatorAction(?string $message = null): bool
    {
        if (! $this->evaluatorActionsDisabled) {
            return false;
        }

        $message ??= PreventsSelfAssessment::DEFAULT_MESSAGE;

        if (method_exists($this, 'setDraftSaveFeedback')) {
            $this->setDraftSaveFeedback('error', $message);
        } elseif (property_exists($this, 'summaryError')) {
            $this->summaryError = $message;
        }

        return true;
    }
}
