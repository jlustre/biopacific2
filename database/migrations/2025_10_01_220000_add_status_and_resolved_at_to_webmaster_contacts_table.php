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
        Schema::table('webmaster_contacts', function (Blueprint $table) {
            $table->string('status')->default('open')->after('is_read');
            $table->timestamp('resolved_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webmaster_contacts', function (Blueprint $table) {
            $table->dropColumn(['status', 'resolved_at']);
        });
    }
};
