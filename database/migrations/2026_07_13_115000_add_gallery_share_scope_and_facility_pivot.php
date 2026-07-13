<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('share_scope', 20)->default('facility')->after('visibility');
        });

        Schema::create('facility_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['gallery_id', 'facility_id']);
            $table->index('facility_id');
        });

        DB::table('galleries')->whereNull('share_scope')->update(['share_scope' => 'facility']);
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_gallery');

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('share_scope');
        });
    }
};
