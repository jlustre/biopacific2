<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('audit_logs', 'facility_id')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('facility_id')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE audit_logs MODIFY facility_id BIGINT UNSIGNED NULL');
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('facility_id')->references('id')->on('facilities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('audit_logs', 'facility_id')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('facility_id')->nullable(false)->change();
            });
        } else {
            DB::statement('ALTER TABLE audit_logs MODIFY facility_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('facility_id')->references('id')->on('facilities')->cascadeOnDelete();
        });
    }
};
