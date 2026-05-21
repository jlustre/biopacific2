<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const JOB_COLUMNS = [
        'hourly_status_id',
        'std_hrs_week',
        'compensation_rate_id',
        'amount',
    ];

    public function up(): void
    {
        if (Schema::hasTable('bp_emp_job_data')) {
            Schema::table('bp_emp_job_data', function (Blueprint $table) {
                if (!Schema::hasColumn('bp_emp_job_data', 'hourly_status_id')) {
                    $table->unsignedBigInteger('hourly_status_id')->nullable()->after('full_part_time');
                }
                if (!Schema::hasColumn('bp_emp_job_data', 'std_hrs_week')) {
                    $table->integer('std_hrs_week')->nullable()->after('hourly_status_id');
                }
                if (!Schema::hasColumn('bp_emp_job_data', 'compensation_rate_id')) {
                    $table->unsignedBigInteger('compensation_rate_id')->nullable()->after('std_hrs_week');
                }
                if (!Schema::hasColumn('bp_emp_job_data', 'amount')) {
                    $table->decimal('amount', 15, 2)->nullable()->after('compensation_rate_id');
                }
            });
        }

        if (Schema::hasTable('bp_employees') && Schema::hasTable('bp_emp_job_data')) {
            $this->migrateEmployeeJobColumnsToJobData();
        }

        if (Schema::hasTable('import_mapping_presets')) {
            DB::table('import_mapping_presets')->orderBy('id')->each(function ($preset) {
                $mappings = $preset->mappings;
                if (is_string($mappings)) {
                    $mappings = json_decode($mappings, true);
                }
                if (!is_array($mappings)) {
                    return;
                }

                $changed = false;
                foreach ($mappings as &$map) {
                    if (($map['table'] ?? '') !== 'bp_employees') {
                        continue;
                    }
                    if (!in_array($map['table_column'] ?? '', self::JOB_COLUMNS, true)) {
                        continue;
                    }
                    $map['table'] = 'bp_emp_job_data';
                    $changed = true;
                }
                unset($map);

                if ($changed) {
                    DB::table('import_mapping_presets')
                        ->where('id', $preset->id)
                        ->update(['mappings' => json_encode($mappings)]);
                }
            });
        }

        if (Schema::hasTable('bp_employees')) {
            Schema::table('bp_employees', function (Blueprint $table) {
                $drops = [];
                foreach (self::JOB_COLUMNS as $column) {
                    if (Schema::hasColumn('bp_employees', $column)) {
                        $drops[] = $column;
                    }
                }
                if (!empty($drops)) {
                    $table->dropColumn($drops);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bp_employees')) {
            Schema::table('bp_employees', function (Blueprint $table) {
                if (!Schema::hasColumn('bp_employees', 'hourly_status_id')) {
                    $table->unsignedBigInteger('hourly_status_id')->nullable()->after('action_id');
                }
                if (!Schema::hasColumn('bp_employees', 'std_hrs_week')) {
                    $table->integer('std_hrs_week')->nullable()->after('hourly_status_id');
                }
                if (!Schema::hasColumn('bp_employees', 'compensation_rate_id')) {
                    $table->unsignedBigInteger('compensation_rate_id')->nullable()->after('std_hrs_week');
                }
                if (!Schema::hasColumn('bp_employees', 'amount')) {
                    $table->decimal('amount', 15, 2)->nullable()->after('compensation_rate_id');
                }
            });
        }

        if (Schema::hasTable('bp_emp_job_data') && Schema::hasTable('bp_employees')) {
            $latestJobRows = DB::table('bp_emp_job_data')
                ->select('bp_emp_job_data.*')
                ->joinSub(
                    DB::table('bp_emp_job_data')
                        ->select('employee_num', DB::raw('MAX(effdt) as max_effdt'))
                        ->groupBy('employee_num'),
                    'latest_effdt',
                    function ($join) {
                        $join->on('bp_emp_job_data.employee_num', '=', 'latest_effdt.employee_num')
                            ->on('bp_emp_job_data.effdt', '=', 'latest_effdt.max_effdt');
                    }
                )
                ->orderByDesc('bp_emp_job_data.effseq')
                ->get()
                ->unique('employee_num');

            foreach ($latestJobRows as $jobRow) {
                $updates = [];
                foreach (self::JOB_COLUMNS as $column) {
                    if ($jobRow->{$column} !== null) {
                        $updates[$column] = $jobRow->{$column};
                    }
                }
                if (!empty($updates)) {
                    DB::table('bp_employees')
                        ->where('employee_num', $jobRow->employee_num)
                        ->update($updates);
                }
            }
        }

        if (Schema::hasTable('bp_emp_job_data')) {
            Schema::table('bp_emp_job_data', function (Blueprint $table) {
                $drops = [];
                foreach (self::JOB_COLUMNS as $column) {
                    if (Schema::hasColumn('bp_emp_job_data', $column)) {
                        $drops[] = $column;
                    }
                }
                if (!empty($drops)) {
                    $table->dropColumn($drops);
                }
            });
        }
    }

    protected function migrateEmployeeJobColumnsToJobData(): void
    {
        $legacyColumns = array_values(array_filter(
            self::JOB_COLUMNS,
            fn (string $column) => Schema::hasColumn('bp_employees', $column)
        ));

        if ($legacyColumns === []) {
            return;
        }

        $employees = DB::table('bp_employees')->get(array_merge(['employee_num'], $legacyColumns));

        foreach ($employees as $employee) {
            $jobValues = [];
            foreach ($legacyColumns as $column) {
                if ($employee->{$column} !== null) {
                    $jobValues[$column] = $employee->{$column};
                }
            }
            if ($jobValues === []) {
                continue;
            }

            $latest = DB::table('bp_emp_job_data')
                ->where('employee_num', $employee->employee_num)
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();

            if ($latest) {
                DB::table('bp_emp_job_data')
                    ->where('assign_id', $latest->assign_id)
                    ->update(array_merge($jobValues, ['updated_at' => now()]));
                continue;
            }

            $userId = 1;
            DB::table('bp_emp_job_data')->insert(array_merge($jobValues, [
                'employee_num' => $employee->employee_num,
                'effdt' => now()->toDateString(),
                'effseq' => 0,
                'created_by' => $userId,
                'updated_by' => $userId,
                'reg_temp' => 'r',
                'full_part_time' => 'ft',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
