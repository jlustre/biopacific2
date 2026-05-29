<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('submission_reason')->nullable()->after('comments');
            $table->string('verification_status')->nullable()->after('submission_reason');
            $table->timestamp('submitted_for_review_at')->nullable()->after('verification_status');
            $table->foreignId('verified_by_user_id')->nullable()->after('submitted_for_review_at')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by_user_id');
            $table->text('verification_notes')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by_user_id');
            $table->dropColumn([
                'submission_reason',
                'verification_status',
                'submitted_for_review_at',
                'verified_at',
                'verification_notes',
            ]);
        });
    }
};
