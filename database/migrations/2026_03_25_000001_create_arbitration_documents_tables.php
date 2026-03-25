<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('facility_arbitration_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->string('template_path'); // Path to the template file
            $table->enum('template_type', ['docx', 'pdf']);
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });

        Schema::create('applicant_arbitration_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_application_id');
            $table->unsignedBigInteger('facility_id');
            $table->string('file_path'); // Path to the filled document
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('applicant_arbitration_documents');
        Schema::dropIfExists('facility_arbitration_documents');
    }
};
