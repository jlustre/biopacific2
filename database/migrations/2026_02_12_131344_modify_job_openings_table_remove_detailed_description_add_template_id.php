<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            // Drop the detailed_description column if it exists
            if (Schema::hasColumn('job_openings', 'detailed_description')) {
                $table->dropColumn('detailed_description');
            }
            
            // Add foreign key to job_description_templates
            $table->unsignedBigInteger('job_description_template_id')->nullable()->after('description');
            $table->foreign('job_description_template_id')
                ->references('id')
                ->on('job_description_templates')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['job_description_template_id']);
            
            // Drop the job_description_template_id column
            $table->dropColumn('job_description_template_id');
            
            // Add back the detailed_description column
            $table->longText('detailed_description')->nullable()->after('description');
        });
    }
};
