<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bp_emp_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('doc_name');
            $table->unsignedBigInteger('doc_type_id');
            $table->boolean('on_file')->default(false);
            $table->date('verified_dt')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->date('exp_dt')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('need_tracking')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bp_emp_checklists');
    }
};
