<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_training_completions', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('notes');
            $table->foreignId('started_by')->nullable()->after('started_at')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('started_by');
            $table->foreignId('submitted_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('submitted_by');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('reviewed_by');
            $table->unsignedBigInteger('review_task_id')->nullable()->after('rejection_reason');
        });

        // Legacy pending → not_started (default before employee begins)
        DB::table('employee_training_completions')
            ->where('status', 'pending')
            ->update(['status' => 'not_started']);
    }

    public function down(): void
    {
        DB::table('employee_training_completions')
            ->where('status', 'not_started')
            ->update(['status' => 'pending']);

        Schema::table('employee_training_completions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('started_by');
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn([
                'started_at',
                'submitted_at',
                'reviewed_at',
                'rejection_reason',
                'review_task_id',
            ]);
        });
    }
};
