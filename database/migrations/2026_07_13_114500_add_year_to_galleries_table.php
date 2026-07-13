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
            $table->unsignedSmallInteger('year')->nullable()->after('title');
            $table->index(['facility_id', 'year']);
        });

        $currentYear = (int) now()->year;

        DB::table('galleries')->orderBy('id')->chunkById(100, function ($rows) use ($currentYear) {
            foreach ($rows as $row) {
                $year = null;

                if (preg_match('/\b(19|20)\d{2}\b/', (string) $row->title, $matches)) {
                    $year = (int) $matches[0];
                } elseif (! empty($row->event_id)) {
                    $eventDate = DB::table('events')->where('id', $row->event_id)->value('event_date');
                    if ($eventDate) {
                        $year = (int) date('Y', strtotime($eventDate));
                    }
                }

                if (! $year && ! empty($row->created_at)) {
                    $year = (int) date('Y', strtotime($row->created_at));
                }

                DB::table('galleries')->where('id', $row->id)->update([
                    'year' => $year ?: $currentYear,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropIndex(['facility_id', 'year']);
            $table->dropColumn('year');
        });
    }
};
