<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_performance_assessments', 'total_score')) {
                $table->unsignedInteger('total_score')->default(0)->after('items');
            }
            if (! Schema::hasColumn('employee_performance_assessments', 'average_score')) {
                $table->decimal('average_score', 6, 2)->default(0)->after('total_score');
            }
            if (! Schema::hasColumn('employee_performance_assessments', 'overall_rating')) {
                $table->string('overall_rating', 32)->nullable()->after('average_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $columns = ['total_score', 'average_score', 'overall_rating'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('employee_performance_assessments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
