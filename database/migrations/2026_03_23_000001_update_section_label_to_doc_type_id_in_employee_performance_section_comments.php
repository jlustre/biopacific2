<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up()
    {
        Schema::table('employee_performance_section_comments', function (Blueprint $table) {
            // Add doc_type_id column
            $table->unsignedBigInteger('doc_type_id')->nullable()->after('assessment_period_id');
            // If you want to enforce FK constraint, uncomment below:
            // $table->foreign('doc_type_id')->references('id')->on('doc_types')->onDelete('set null');
        });
        // Migrate section_label data to doc_type_id
        // This assumes section_label matches DocType name
        $comments = DB::table('employee_performance_section_comments')->get();
        foreach ($comments as $comment) {
            $docType = DB::table('doc_types')->where('name', $comment->section_label)->first();
            if ($docType) {
                DB::table('employee_performance_section_comments')
                    ->where('id', $comment->id)
                    ->update(['doc_type_id' => $docType->id]);
            }
        }
        // Drop section_label column
        Schema::table('employee_performance_section_comments', function (Blueprint $table) {
            $table->dropColumn('section_label');
        });
    }
    public function down()
    {
        Schema::table('employee_performance_section_comments', function (Blueprint $table) {
            $table->string('section_label')->nullable()->after('assessment_period_id');
        });
        // Optionally, you could repopulate section_label from doc_type_id if needed
        // (not implemented here)
        Schema::table('employee_performance_section_comments', function (Blueprint $table) {
            $table->dropColumn('doc_type_id');
        });
    }
};
