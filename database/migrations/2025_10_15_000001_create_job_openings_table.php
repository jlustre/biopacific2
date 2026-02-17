<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('detailed_description')->nullable();
            $table->string('reporting_to')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('salary_range')->nullable();
            $table->enum('salary_unit', ['hourly', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->date('posted_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->string('status')->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
