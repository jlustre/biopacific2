<?php

namespace App\Mail;

use App\Models\BPEmployee;
use App\Models\EmployeeTrainingItem;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrainingTaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BPEmployee $employee,
        public EmployeeTrainingItem $trainingItem,
        public User $reviewer,
        public string $taskTitle,
        public string $taskMessage,
        public string $actionUrl,
        public ?string $dueDate = null,
    ) {}

    public function build(): self
    {
        $employeeName = trim(($this->employee->first_name ?? '').' '.($this->employee->last_name ?? '')) ?: 'Employee';

        return $this->subject($this->taskTitle)
            ->view('emails.training-task-assigned', [
                'employeeName' => $employeeName,
                'trainingName' => $this->trainingItem->name,
                'reviewerName' => $this->reviewer->name,
                'taskTitle' => $this->taskTitle,
                'taskMessage' => $this->taskMessage,
                'actionUrl' => $this->actionUrl,
                'dueDate' => $this->dueDate,
            ]);
    }
}
