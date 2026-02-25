<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_checklist', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('notes');
            $table->timestamp('returned_at')->nullable()->after('submitted_at');
            $table->foreignId('returned_by')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_checklist', function (Blueprint $table) {
            $table->dropForeign(['returned_by']);
            $table->dropColumn(['submitted_at', 'returned_at', 'returned_by']);
        });
    }
};
