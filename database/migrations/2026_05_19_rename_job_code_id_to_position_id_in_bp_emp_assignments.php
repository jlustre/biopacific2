<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('bp_emp_job_data', function (Blueprint $table) {
            $table->renameColumn('job_code_id', 'position_id');
        });
    }

    public function down()
    {
        Schema::table('bp_emp_job_data', function (Blueprint $table) {
            $table->renameColumn('position_id', 'job_code_id');
        });
    }
};
