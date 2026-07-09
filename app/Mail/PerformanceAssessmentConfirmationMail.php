<?php



namespace App\Mail;



use App\Models\BPEmployee;

use Illuminate\Bus\Queueable;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;



class PerformanceAssessmentConfirmationMail extends Mailable

{

    use Queueable, SerializesModels;



    public function __construct(

        public Model $assessment,

        public BPEmployee $employee,

        public string $confirmationUrl,

        public ?string $periodLabel = null,

        public string $assessmentKind = 'performance',

        public ?string $facilityName = null,

        public string $notificationPurpose = 'employee_confirmation',

        public ?string $sectionLabel = null,

    ) {

    }



    public function build(): self

    {

        $employeeName = trim(($this->employee->first_name ?? '').' '.($this->employee->last_name ?? ''));

        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';

        $facilityName = $this->facilityName ?? config('app.name');



        $isCompetency = $this->assessmentKind === 'competency';

        $assessmentLabel = $isCompetency ? 'competency assessment' : 'performance appraisal';

        if ($this->sectionLabel) {
            $assessmentLabel = $this->sectionLabel;
        }



        if ($this->notificationPurpose === 'reviewer_returned') {

            $subject = $isCompetency

                ? 'Action required: employee sent competency assessment back for updates'

                : 'Action required: employee sent performance appraisal back for updates';

        } elseif ($this->notificationPurpose === 'reviewer_approval') {

            $subject = $isCompetency

                ? 'Action required: employee signed competency assessment for your approval'

                : 'Action required: employee acknowledged performance appraisal for your approval';

        } elseif ($this->notificationPurpose === 'employee_resubmitted') {

            $subject = $isCompetency

                ? 'Action required: updated competency assessment ready for your review'

                : 'Action required: updated performance appraisal ready for your review';

        } else {

            $subject = $isCompetency

                ? 'Action required: confirm your competency assessment'

                : 'Action required: confirm your performance appraisal';

        }



        return $this

            ->subject($subject)

            ->markdown('emails.performance_assessment_confirmation', [

                'employeeName' => $employeeName,

                'facilityName' => $facilityName,

                'assessmentLabel' => $assessmentLabel,

                'periodLabel' => $this->periodLabel,

                'confirmationUrl' => $this->confirmationUrl,

                'notificationPurpose' => $this->notificationPurpose,

                'sectionLabel' => $this->sectionLabel,

            ]);

    }

}

