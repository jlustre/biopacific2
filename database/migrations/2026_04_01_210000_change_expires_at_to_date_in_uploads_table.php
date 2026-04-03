<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('uploads', function (Blueprint $table) {
            // Change expires_at from timestamp to date
            $table->date('expires_at')->nullable()->change();
        });
    }
    public function down() {
        Schema::table('uploads', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->change();
        });
    }
};
