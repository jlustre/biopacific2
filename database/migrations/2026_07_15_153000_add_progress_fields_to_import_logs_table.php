<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->unsignedInteger('total_rows')->default(0)->after('status');
            $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
            $table->unsignedInteger('imported_rows')->default(0)->after('processed_rows');
            $table->unsignedInteger('skipped_rows')->default(0)->after('imported_rows');
            $table->unsignedInteger('failed_rows')->default(0)->after('skipped_rows');
            $table->string('import_file_path')->nullable()->after('source_filename');
            $table->timestamp('cancel_requested_at')->nullable()->after('completed_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropColumn([
                'total_rows',
                'processed_rows',
                'imported_rows',
                'skipped_rows',
                'failed_rows',
                'import_file_path',
                'cancel_requested_at',
                'cancelled_at',
            ]);
        });
    }
};
