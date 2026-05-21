<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyColumnMap = [
            'federal_tax_data_id' => 'fed_tax_data',
            'state_tax_data_id' => 'state_tax_data',
            'local_tax_data_id' => 'local_withholding_allowance',
        ];

        if (Schema::hasTable('import_mapping_presets')) {
            DB::table('import_mapping_presets')->orderBy('id')->each(function ($preset) use ($legacyColumnMap) {
                $mappings = $preset->mappings;
                if (is_string($mappings)) {
                    $mappings = json_decode($mappings, true);
                }
                if (!is_array($mappings)) {
                    return;
                }

                $changed = false;
                foreach ($mappings as &$map) {
                    $table = $map['table'] ?? '';
                    $column = $map['table_column'] ?? '';
                    if ($table !== 'bp_employees' || !isset($legacyColumnMap[$column])) {
                        continue;
                    }
                    $map['table'] = 'bp_emp_tax_data';
                    $map['table_column'] = $legacyColumnMap[$column];
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
                foreach (['federal_tax_data_id', 'state_tax_data_id', 'local_tax_data_id'] as $column) {
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
                if (!Schema::hasColumn('bp_employees', 'federal_tax_data_id')) {
                    $table->unsignedBigInteger('federal_tax_data_id')->nullable()->after('action_id');
                }
                if (!Schema::hasColumn('bp_employees', 'state_tax_data_id')) {
                    $table->unsignedBigInteger('state_tax_data_id')->nullable()->after('federal_tax_data_id');
                }
                if (!Schema::hasColumn('bp_employees', 'local_tax_data_id')) {
                    $table->unsignedBigInteger('local_tax_data_id')->nullable()->after('state_tax_data_id');
                }
            });
        }
    }
};
