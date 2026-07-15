<?php

use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->timestamp('superseded_at')->nullable()->after('verification_notes');
            $table->foreignId('superseded_by_upload_id')
                ->nullable()
                ->after('superseded_at')
                ->constrained('uploads')
                ->nullOnDelete();
            $table->unsignedSmallInteger('coverage_year')->nullable()->after('superseded_by_upload_id');
            $table->index(['employee_num', 'upload_type_id', 'coverage_year', 'superseded_at'], 'uploads_current_year_idx');
        });

        // Backfill coverage years and keep one current row per employee/type/year.
        Upload::query()->orderBy('id')->chunkById(200, function ($uploads) {
            foreach ($uploads as $upload) {
                $expires = $upload->expires_at ? Carbon::parse($upload->expires_at) : null;
                $effective = $upload->effective_start_date ? Carbon::parse($upload->effective_start_date) : null;
                $uploaded = $upload->uploaded_at ? Carbon::parse($upload->uploaded_at) : null;

                $year = $expires?->year
                    ?? $effective?->year
                    ?? $uploaded?->year
                    ?? (int) now()->year;

                DB::table('uploads')->where('id', $upload->id)->update(['coverage_year' => $year]);
            }
        });

        $groups = DB::table('uploads')
            ->select('employee_num', 'upload_type_id', 'coverage_year')
            ->whereNotNull('upload_type_id')
            ->whereNull('superseded_at')
            ->groupBy('employee_num', 'upload_type_id', 'coverage_year')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $rows = DB::table('uploads')
                ->where('employee_num', $group->employee_num)
                ->where('upload_type_id', $group->upload_type_id)
                ->where('coverage_year', $group->coverage_year)
                ->whereNull('superseded_at')
                ->orderByDesc('uploaded_at')
                ->orderByDesc('id')
                ->get(['id']);

            $keepId = $rows->first()?->id;
            if (! $keepId) {
                continue;
            }

            $supersedeIds = $rows->pluck('id')->filter(fn ($id) => (int) $id !== (int) $keepId)->all();
            if ($supersedeIds === []) {
                continue;
            }

            DB::table('uploads')
                ->whereIn('id', $supersedeIds)
                ->update([
                    'superseded_at' => now(),
                    'superseded_by_upload_id' => $keepId,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropIndex('uploads_current_year_idx');
            $table->dropConstrainedForeignId('superseded_by_upload_id');
            $table->dropColumn(['superseded_at', 'coverage_year']);
        });
    }
};
