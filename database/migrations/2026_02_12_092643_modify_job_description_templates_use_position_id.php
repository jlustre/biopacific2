<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add position_id column (nullable for now)
        Schema::table('job_description_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('id');
        });

        // Step 2: Migrate existing data - map title to position_id
        $templates = DB::table('job_description_templates')->whereNotNull('title')->get();
        foreach ($templates as $template) {
            $position = DB::table('positions')->where('title', $template->title)->first();
            if ($position) {
                DB::table('job_description_templates')
                    ->where('id', $template->id)
                    ->update(['position_id' => $position->id]);
            }
        }

        // Step 3: Make position_id required and add foreign key
        Schema::table('job_description_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable(false)->change();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
        });

        // Step 4: Drop title column
        Schema::table('job_description_templates', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add title column back
        Schema::table('job_description_templates', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
        });

        // Step 2: Restore title from position_id
        $templates = DB::table('job_description_templates')->whereNotNull('position_id')->get();
        foreach ($templates as $template) {
            $position = DB::table('positions')->where('id', $template->position_id)->first();
            if ($position) {
                DB::table('job_description_templates')
                    ->where('id', $template->id)
                    ->update(['title' => $position->title]);
            }
        }

        // Step 3: Drop foreign key and position_id column
        Schema::table('job_description_templates', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
};
