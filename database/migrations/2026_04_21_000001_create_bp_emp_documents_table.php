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
        Schema::create('bp_emp_documents', function (Blueprint $table) {
            $table->id('document_id');
            $table->string('employee_num'); // Foreign key to bp_employees.employee_num
            $table->string('document_type', 100);
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('effdt'); // Effective date for the document
            $table->integer('effseq')->default(0); // Effective sequence for the document
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('employee_num')->references('employee_num')->on('bp_employees')->onDelete('cascade');
            $table->index(['employee_num', 'effdt', 'effseq'], 'idx_emp_document_hist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_documents');
    }
};
