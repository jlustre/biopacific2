<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_hr_status', 32)->default('incomplete')->after('avatar_path');
            $table->timestamp('profile_submitted_at')->nullable()->after('profile_hr_status');
            $table->timestamp('profile_confirmed_at')->nullable()->after('profile_submitted_at');
            $table->foreignId('profile_confirmed_by')->nullable()->after('profile_confirmed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_confirmed_by');
            $table->dropColumn(['profile_hr_status', 'profile_submitted_at', 'profile_confirmed_at']);
        });
    }
};
