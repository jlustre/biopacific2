<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            if (!Schema::hasColumn('facilities', 'domain')) {
                $table->string('domain')->nullable()->after('id');
            }
            if (!Schema::hasColumn('facilities', 'subdomain')) {
                $table->string('subdomain')->nullable()->after('domain');
            }
            if (!Schema::hasColumn('facilities', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('subdomain');
            }
            if (!Schema::hasColumn('facilities', 'settings')) {
                $table->json('settings')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('facilities', 'layout_template')) {
                $table->string('layout_template')->default('layout1')->after('settings');
            }
            if (!Schema::hasColumn('facilities', 'layout_config')) {
                $table->json('layout_config')->nullable()->after('layout_template');
            }
        });

        // Just ensure the domain column is nullable if needed
        $facilitiesWithoutDomain = \App\Models\Facility::whereNull('domain')->get();
        foreach ($facilitiesWithoutDomain as $index => $facility) {
            $facility->update([
                'domain' => $facility->slug ? $facility->slug . '.example.com' : 'facility' . ($index + 1) . '.example.com',
                'subdomain' => $facility->slug ?: 'facility' . ($index + 1),
                'is_active' => true,
                'layout_template' => 'layout1'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropIndex(['domain', 'is_active']);
            $table->dropIndex(['layout_template']);
            $table->dropColumn([
                'domain',
                'subdomain',
                'is_active',
                'settings',
                'layout_template',
                'layout_config'
            ]);
        });
    }
};
