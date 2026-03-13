<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->date('employment_from')->nullable();
            $table->date('employment_to')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('salary_per')->nullable(); // year or hour
            $table->text('duties_description')->nullable();
            $table->text('performance_description')->nullable();
            $table->date('date_contacted')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->dropColumn([
                'employment_from',
                'employment_to',
                'salary',
                'salary_per',
                'duties_description',
                'performance_description',
                'date_contacted',
            ]);
        });
    }
};
