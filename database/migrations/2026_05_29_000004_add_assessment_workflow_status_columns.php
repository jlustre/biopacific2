<?php

use App\Support\AssessmentWorkflowStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employee_performance_assessments', 'status')) {
            Schema::table('employee_performance_assessments', function (Blueprint $table) {
                $table->string('status', 64)
                    ->default(AssessmentWorkflowStatus::DRAFT)
                    ->after('finalized');
            });
        }

        DB::table('employee_performance_assessments')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $status = ! empty($row->finalized)
                    ? AssessmentWorkflowStatus::COMPLETED
                    : (filled($row->acknowledge_dt)
                        ? AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL
                        : AssessmentWorkflowStatus::DRAFT);

                DB::table('employee_performance_assessments')
                    ->where('id', $row->id)
                    ->update(['status' => $status]);
            }
        });

        DB::table('employee_competency_assessments')
            ->where('status', 'for_employee_signature')
            ->update(['status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION]);

        DB::table('employee_competency_assessments')
            ->where('status', 'for_reviewer_signature')
            ->update(['status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL]);
    }

    public function down(): void
    {
        DB::table('employee_competency_assessments')
            ->where('status', AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION)
            ->update(['status' => 'for_employee_signature']);

        DB::table('employee_competency_assessments')
            ->where('status', AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL)
            ->update(['status' => 'for_reviewer_signature']);

        if (Schema::hasColumn('employee_performance_assessments', 'status')) {
            Schema::table('employee_performance_assessments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
