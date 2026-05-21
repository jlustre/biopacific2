<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('facility_id');
            $table->foreignId('import_mapping_preset_id')->nullable()->constrained('import_mapping_presets')->nullOnDelete();
            $table->string('source', 32)->default('facility');
            $table->string('source_filename')->nullable();
            $table->string('status', 32)->default('running');
            $table->json('tables_affected')->nullable();
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('can_revert')->default(false);
            $table->timestamp('reverted_at')->nullable();
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['facility_id', 'created_at']);
            $table->index('status');
        });

        Schema::create('import_log_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->cascadeOnDelete();
            $table->string('table_name', 64);
            $table->string('employee_num', 64)->nullable()->index();
            $table->string('action', 16);
            $table->json('record_key');
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->timestamps();

            $table->index(['import_log_id', 'table_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_log_changes');
        Schema::dropIfExists('import_logs');
    }
};
