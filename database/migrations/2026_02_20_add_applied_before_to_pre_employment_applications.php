<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            $table->boolean('applied_here_before')->nullable()->after('worked_here_when_where');
            $table->text('applied_here_when_where')->nullable()->after('applied_here_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            $table->dropColumn(['applied_here_before', 'applied_here_when_where']);
        });
    }
};
