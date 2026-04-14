<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('report_id');
            $table->json('parameters')->nullable();
            $table->json('notify_roles')->nullable();
            $table->text('notify_emails')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('notifications_enabled')->default(false);
            $table->string('report_format')->default('csv');
            $table->string('cron_expression');
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->enum('status', ['active', 'paused', 'error'])->default('active');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
