<?php

use App\Support\ContentVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('visibility', 20)->default(ContentVisibility::BOTH);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['facility_id', 'slug']);
            $table->index(['facility_id', 'is_active', 'sort_order']);
            $table->index('created_by');
        });

        Schema::table('gallery_images', function (Blueprint $table) {
            $table->foreignId('gallery_id')->nullable()->after('facility_id')->constrained('galleries')->nullOnDelete();
            $table->string('caption', 500)->nullable()->after('description');
            $table->foreignId('created_by')->nullable()->after('visibility')->constrained('users')->nullOnDelete();
        });

        $facilityIds = DB::table('gallery_images')
            ->whereNotNull('facility_id')
            ->distinct()
            ->pluck('facility_id');

        foreach ($facilityIds as $facilityId) {
            $facilityName = DB::table('facilities')->where('id', $facilityId)->value('name') ?: 'Facility';
            $title = trim($facilityName).' Gallery';
            $slugBase = Str::slug($title) ?: 'gallery';
            $slug = $slugBase;
            $n = 1;
            while (DB::table('galleries')->where('facility_id', $facilityId)->where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.$n++;
            }

            $galleryId = DB::table('galleries')->insertGetId([
                'facility_id' => $facilityId,
                'event_id' => null,
                'title' => $title,
                'slug' => $slug,
                'description' => 'Migrated photos for this facility.',
                'visibility' => ContentVisibility::BOTH,
                'is_active' => true,
                'sort_order' => 0,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('gallery_images')
                ->where('facility_id', $facilityId)
                ->whereNull('gallery_id')
                ->update(['gallery_id' => $galleryId]);

            DB::table('gallery_images')
                ->where('gallery_id', $galleryId)
                ->whereNull('caption')
                ->whereNotNull('description')
                ->update(['caption' => DB::raw('description')]);
        }
    }

    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('caption');
            $table->dropConstrainedForeignId('gallery_id');
        });

        Schema::dropIfExists('galleries');
    }
};
