<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('bp_employees', 'original_hire_dt')) {
                $table->date('original_hire_dt')->nullable()->after('dob');
            }
        });
    }

    public function down()
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            if (Schema::hasColumn('bp_employees', 'original_hire_dt')) {
                $table->dropColumn('original_hire_dt');
            }
        });
    }
};
