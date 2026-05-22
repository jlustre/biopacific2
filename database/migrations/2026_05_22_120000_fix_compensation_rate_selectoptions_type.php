<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('selectoptions') || !Schema::hasTable('optionstypes')) {
            return;
        }

        $compensationTypeId = DB::table('optionstypes')->where('name', 'Compensation Rate')->value('id');
        if (!$compensationTypeId) {
            return;
        }

        $names = ['Hourly', 'Annual', 'Biweekly', 'Monthly', 'Salary'];
        $sort = 1;
        foreach ($names as $name) {
            $existing = DB::table('selectoptions')
                ->where('name', $name)
                ->orderBy('id')
                ->first();

            if ($existing) {
                DB::table('selectoptions')
                    ->where('id', $existing->id)
                    ->update([
                        'type_id' => $compensationTypeId,
                        'value' => $name,
                        'isActive' => 1,
                        'sort_order' => $sort,
                    ]);
            } else {
                DB::table('selectoptions')->insert([
                    'type_id' => $compensationTypeId,
                    'name' => $name,
                    'value' => $name,
                    'isActive' => 1,
                    'sort_order' => $sort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $sort++;
        }
    }

    public function down(): void
    {
        // No rollback — options may already be referenced by job data imports.
    }
};
