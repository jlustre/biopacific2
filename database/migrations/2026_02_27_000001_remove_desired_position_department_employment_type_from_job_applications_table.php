<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['desired_position', 'department', 'employment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('desired_position')->nullable()->after('job_opening_id');
            $table->string('department')->nullable()->after('desired_position');
            $table->string('employment_type', 20)->nullable()->after('department');
        });
    }
};
