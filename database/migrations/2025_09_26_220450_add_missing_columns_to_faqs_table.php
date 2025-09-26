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
        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('facility_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true)->after('category');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_default')->default(false)->after('is_featured');
            $table->integer('sort_order')->default(0)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropColumn(['facility_id', 'is_active', 'is_featured', 'is_default', 'sort_order']);
        });
    }
};
