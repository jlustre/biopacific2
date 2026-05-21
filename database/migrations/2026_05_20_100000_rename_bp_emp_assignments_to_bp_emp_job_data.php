<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bp_emp_assignments') && !Schema::hasTable('bp_emp_job_data')) {
            Schema::rename('bp_emp_assignments', 'bp_emp_job_data');
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
                    if (($map['table'] ?? '') === 'bp_emp_assignments') {
                        $map['table'] = 'bp_emp_job_data';
                        $changed = true;
                    }
                }
                unset($map);

                if ($changed) {
                    DB::table('import_mapping_presets')
                        ->where('id', $preset->id)
                        ->update(['mappings' => json_encode($mappings)]);
                }
            });
        }

        if (Schema::hasTable('import_log_changes')) {
            DB::table('import_log_changes')
                ->where('table_name', 'bp_emp_assignments')
                ->update(['table_name' => 'bp_emp_job_data']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('import_log_changes')) {
            DB::table('import_log_changes')
                ->where('table_name', 'bp_emp_job_data')
                ->update(['table_name' => 'bp_emp_assignments']);
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
                    if (($map['table'] ?? '') === 'bp_emp_job_data') {
                        $map['table'] = 'bp_emp_assignments';
                        $changed = true;
                    }
                }
                unset($map);

                if ($changed) {
                    DB::table('import_mapping_presets')
                        ->where('id', $preset->id)
                        ->update(['mappings' => json_encode($mappings)]);
                }
            });
        }

        if (Schema::hasTable('bp_emp_job_data') && !Schema::hasTable('bp_emp_assignments')) {
            Schema::rename('bp_emp_job_data', 'bp_emp_assignments');
        }
    }
};
