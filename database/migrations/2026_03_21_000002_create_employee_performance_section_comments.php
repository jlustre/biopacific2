<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('employee_performance_section_comments', function (Blueprint $table) {
			$table->id();
			$table->string('emp_id');
			$table->unsignedBigInteger('assessment_period_id');
			$table->string('section_label');
			$table->text('comment')->nullable();
			$table->timestamps();
			$table->foreign('assessment_period_id', 'epsec_apid_fk')->references('id')->on('employee_assessment_periods')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::dropIfExists('employee_performance_section_comments');
	}
};
