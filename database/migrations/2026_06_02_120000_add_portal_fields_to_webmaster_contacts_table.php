<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webmaster_contacts', function (Blueprint $table) {
            $table->string('category', 32)->default('issue')->after('facility_id');
            $table->string('source', 32)->default('public_website')->after('category');
            $table->foreignId('user_id')->nullable()->after('source')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('webmaster_contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['category', 'source']);
        });
    }
};
