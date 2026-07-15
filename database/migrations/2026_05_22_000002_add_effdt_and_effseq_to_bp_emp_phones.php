<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bp_emp_phones')) {
            return;
        }

        Schema::table('bp_emp_phones', function (Blueprint $table) {
            if (!Schema::hasColumn('bp_emp_phones', 'effdt')) {
                $table->date('effdt')->nullable()->after('phone_type');
            }
            if (!Schema::hasColumn('bp_emp_phones', 'effseq')) {
                $table->integer('effseq')->default(0)->after('effdt');
            }
        });

        DB::table('bp_emp_phones')
            ->whereNull('effdt')
            ->update([
                'effdt' => now()->toDateString(),
                'effseq' => 0,
            ]);

        if (Schema::hasColumn('bp_emp_phones', 'effdt')) {
            if (DB::getDriverName() === 'sqlite') {
                Schema::table('bp_emp_phones', function (Blueprint $table) {
                    $table->date('effdt')->nullable(false)->change();
                });
            } else {
                DB::statement('ALTER TABLE `bp_emp_phones` MODIFY `effdt` DATE NOT NULL');
            }
        }

        if (!$this->indexExists('bp_emp_phones', 'idx_bp_emp_phone_hist')) {
            Schema::table('bp_emp_phones', function (Blueprint $table) {
                $table->index(['employee_num', 'effdt', 'effseq'], 'idx_bp_emp_phone_hist');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('bp_emp_phones')) {
            return;
        }

        Schema::table('bp_emp_phones', function (Blueprint $table) {
            if ($this->indexExists('bp_emp_phones', 'idx_bp_emp_phone_hist')) {
                $table->dropIndex('idx_bp_emp_phone_hist');
            }
            if (Schema::hasColumn('bp_emp_phones', 'effseq')) {
                $table->dropColumn('effseq');
            }
            if (Schema::hasColumn('bp_emp_phones', 'effdt')) {
                $table->dropColumn('effdt');
            }
        });
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }
};
