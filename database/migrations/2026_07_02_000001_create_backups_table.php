<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('backup_name');
            $table->string('backup_type', 32);
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->json('included_tables')->nullable();
            $table->json('included_sections')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->foreignId('pre_restore_backup_id')->nullable()->constrained('backups')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('backup_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
