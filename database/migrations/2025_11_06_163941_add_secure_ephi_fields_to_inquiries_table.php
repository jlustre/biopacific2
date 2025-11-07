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
        Schema::table('inquiries', function (Blueprint $table) {
            // Add fields for secure ePHI handling
            $table->string('access_token', 64)->unique()->nullable()->after('no_phi');
            $table->timestamp('token_expires_at')->nullable()->after('access_token');
            $table->boolean('is_viewed')->default(false)->after('token_expires_at');
            $table->timestamp('viewed_at')->nullable()->after('is_viewed');
            $table->string('viewed_by')->nullable()->after('viewed_at');
            $table->boolean('is_encrypted')->default(false)->after('viewed_by');
            $table->text('encryption_key_hint')->nullable()->after('is_encrypted');
            
            // Add index for token lookups
            $table->index(['access_token', 'token_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['access_token', 'token_expires_at']);
            $table->dropColumn([
                'access_token',
                'token_expires_at',
                'is_viewed',
                'viewed_at', 
                'viewed_by',
                'is_encrypted',
                'encryption_key_hint'
            ]);
        });
    }
};
