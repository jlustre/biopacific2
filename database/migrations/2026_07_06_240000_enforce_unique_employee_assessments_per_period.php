<?php

use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deduplicatePerformanceAssessments();
        $this->deduplicateAssessmentPeriods();

        if (! $this->indexExists('employee_performance_assessments', 'epa_emp_period_unique')) {
            Schema::table('employee_performance_assessments', function (Blueprint $table) {
                $table->unique(['employee_num', 'assessment_period_id'], 'epa_emp_period_unique');
            });
        }

        if (! $this->indexExists('employee_assessment_periods', 'eap_emp_dates_unique')) {
            Schema::table('employee_assessment_periods', function (Blueprint $table) {
                $table->unique(['employee_num', 'date_from', 'date_to'], 'eap_emp_dates_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            if ($this->indexExists('employee_performance_assessments', 'epa_emp_period_unique')) {
                $table->dropUnique('epa_emp_period_unique');
            }
        });

        Schema::table('employee_assessment_periods', function (Blueprint $table) {
            if ($this->indexExists('employee_assessment_periods', 'eap_emp_dates_unique')) {
                $table->dropUnique('eap_emp_dates_unique');
            }
        });
    }

    protected function deduplicatePerformanceAssessments(): void
    {
        $duplicateGroups = DB::table('employee_performance_assessments')
            ->whereNotNull('assessment_period_id')
            ->select('employee_num', 'assessment_period_id')
            ->groupBy('employee_num', 'assessment_period_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $assessments = EmployeePerformanceAssessment::query()
                ->where('employee_num', $group->employee_num)
                ->where('assessment_period_id', $group->assessment_period_id)
                ->orderByDesc('id')
                ->get()
                ->sortByDesc(fn (EmployeePerformanceAssessment $assessment) => $this->performanceAssessmentKeepScore($assessment))
                ->values();

            $assessments->slice(1)->each->delete();
        }
    }

    protected function performanceAssessmentKeepScore(EmployeePerformanceAssessment $assessment): int
    {
        $statusScore = match ($assessment->workflowStatus()) {
            AssessmentWorkflowStatus::COMPLETED => 40,
            AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL => 30,
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION => 20,
            default => 10,
        };

        return $statusScore * 1_000_000 + (int) $assessment->id;
    }

    protected function deduplicateAssessmentPeriods(): void
    {
        $duplicateGroups = DB::table('employee_assessment_periods')
            ->select('employee_num', 'date_from', 'date_to')
            ->groupBy('employee_num', 'date_from', 'date_to')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $periodIds = DB::table('employee_assessment_periods')
                ->where('employee_num', $group->employee_num)
                ->where('date_from', $group->date_from)
                ->where('date_to', $group->date_to)
                ->orderBy('id')
                ->pluck('id');

            $keeperId = (int) $periodIds->first();
            $duplicateIds = $periodIds->slice(1)->all();

            if ($duplicateIds === []) {
                continue;
            }

            $this->reassignAssessmentPeriodReferences($keeperId, $duplicateIds);
            DB::table('employee_assessment_periods')->whereIn('id', $duplicateIds)->delete();
        }
    }

    /**
     * @param  list<int>  $duplicatePeriodIds
     */
    protected function reassignAssessmentPeriodReferences(int $keeperPeriodId, array $duplicatePeriodIds): void
    {
        foreach ($duplicatePeriodIds as $duplicatePeriodId) {
            $performance = EmployeePerformanceAssessment::query()
                ->where('assessment_period_id', $duplicatePeriodId)
                ->first();

            if ($performance) {
                $existing = EmployeePerformanceAssessment::query()
                    ->where('employee_num', $performance->employee_num)
                    ->where('assessment_period_id', $keeperPeriodId)
                    ->where('id', '!=', $performance->id)
                    ->exists();

                if ($existing) {
                    $performance->delete();
                } else {
                    $performance->assessment_period_id = $keeperPeriodId;
                    $performance->save();
                }
            }

            $competency = EmployeeCompetencyAssessment::query()
                ->where('assessment_period_id', $duplicatePeriodId)
                ->first();

            if ($competency) {
                $existing = EmployeeCompetencyAssessment::query()
                    ->where('employee_num', $competency->employee_num)
                    ->where('assessment_period_id', $keeperPeriodId)
                    ->where('id', '!=', $competency->id)
                    ->exists();

                if ($existing) {
                    $competency->delete();
                } else {
                    $competency->assessment_period_id = $keeperPeriodId;
                    $competency->save();
                }
            }

            DB::table('employee_assessment_item_entries')
                ->where('assessment_period_id', $duplicatePeriodId)
                ->update(['assessment_period_id' => $keeperPeriodId]);

            DB::table('employee_performance_section_comments')
                ->where('assessment_period_id', $duplicatePeriodId)
                ->update(['assessment_period_id' => $keeperPeriodId]);
        }
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($index) => ($index->name ?? '') === $indexName);
        }

        $database = $connection->getDatabaseName();
        $result = $connection->select(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($result[0]->aggregate ?? 0) > 0;
    }
};
