<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('bp_emp_checklists');
        Schema::create('bp_emp_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('employee_num')->unique();
            $table->json('items'); // All checklist items as JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bp_emp_checklists');
    }
};
