<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEphiSecurityToTourRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_requests', function (Blueprint $table) {
            // Add secure access token for viewing requests
            $table->string('access_token')->unique()->nullable()->after('consent');
            
            // Add expiration timestamp for secure access
            $table->timestamp('expires_at')->nullable()->after('access_token');
            
            // Add viewing timestamp for audit trail
            $table->timestamp('viewed_at')->nullable()->after('expires_at');
            
            // Add audit log for compliance tracking
            $table->json('audit_log')->nullable()->after('viewed_at');
            
            // Make ePHI fields larger to accommodate encryption
            $table->text('full_name')->change();
            $table->text('phone')->change();
            $table->text('email')->change();
            
            // Index for secure access
            $table->index('access_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_requests', function (Blueprint $table) {
            $table->dropIndex(['access_token']);
            $table->dropColumn(['access_token', 'expires_at', 'viewed_at', 'audit_log']);
            
            // Revert field types (this will lose data if encryption is active)
            $table->string('full_name')->change();
            $table->string('phone')->change();
            $table->string('email')->change();
        });
    }
}