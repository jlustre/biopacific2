<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('upload_type_id')->nullable()->after('user_id');
            // If you want to enforce foreign key constraint, uncomment below:
            // $table->foreign('upload_type_id')->references('id')->on('upload_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn('upload_type_id');
        });
    }
};
