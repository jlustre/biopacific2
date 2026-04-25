<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            // Add employee_num column for one-to-many relationship with bp_employees
            if (!Schema::hasColumn('uploads', 'employee_num')) {
                $table->string('employee_num')->nullable()->after('facility_id');
                $table->index('employee_num');
            }
            // Remove effective_end_date column
            if (Schema::hasColumn('uploads', 'effective_end_date')) {
                $table->dropColumn('effective_end_date');
            }
            // Remove description column
            if (Schema::hasColumn('uploads', 'description')) {
                $table->dropColumn('description');
            }
        });
        // Add foreign key constraint after column creation
        Schema::table('uploads', function (Blueprint $table) {
            if (Schema::hasColumn('uploads', 'employee_num')) {
                $table->foreign('employee_num')->references('employee_num')->on('bp_employees')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            if (Schema::hasColumn('uploads', 'employee_num')) {
                $table->dropForeign(['employee_num']);
                $table->dropColumn('employee_num');
            }
            $table->date('effective_end_date')->nullable();
            $table->text('description')->nullable();
        });
    }
};
