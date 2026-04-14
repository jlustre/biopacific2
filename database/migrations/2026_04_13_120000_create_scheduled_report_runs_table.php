<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('scheduled_report_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_report_id')->constrained()->onDelete('cascade');
            $table->timestamp('executed_at');
            $table->string('result_path')->nullable();
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('scheduled_report_runs');
    }
};
