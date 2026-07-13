<?php

namespace App\Mail;

use App\Models\BPEmployee;
use App\Models\EmployeeTrainingCompletion;
use App\Models\EmployeeTrainingItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrainingCompletionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BPEmployee $employee,
        public EmployeeTrainingItem $item,
        public EmployeeTrainingCompletion $completion,
        public string $periodLabel,
    ) {}

    public function build(): self
    {
        $employeeName = trim(($this->employee->first_name ?? '').' '.($this->employee->last_name ?? ''));
        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';
        $subject = 'Training approved: '.$this->item->name;

        $checklistUrl = route('member.checklists').'?'.http_build_query(array_filter([
            'assessment_period_id' => $this->completion->assessment_period_id,
        ]));

        return $this->subject($subject)
            ->view('emails.training-completion-approved', [
                'employeeName' => $employeeName,
                'trainingName' => $this->item->name,
                'periodLabel' => $this->periodLabel,
                'isHiring' => $this->item->isHiring(),
                'checklistUrl' => $checklistUrl,
            ]);
    }
}
