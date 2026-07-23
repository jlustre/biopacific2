<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_types', function (Blueprint $table) {
            if (! Schema::hasColumn('upload_types', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('upload_types', 'doc_type_id')) {
                $table->unsignedBigInteger('doc_type_id')->nullable()->after('checklist_section');
            }
            if (! Schema::hasColumn('upload_types', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('doc_type_id');
            }
            if (! Schema::hasColumn('upload_types', 'applies_to_all_positions')) {
                $table->boolean('applies_to_all_positions')->default(false)->after('sort_order');
            }
        });

        if (Schema::hasTable('doc_types') && Schema::hasColumn('upload_types', 'doc_type_id')) {
            Schema::table('upload_types', function (Blueprint $table) {
                try {
                    $table->foreign('doc_type_id')
                        ->references('id')
                        ->on('doc_types')
                        ->nullOnDelete();
                } catch (\Throwable) {
                    // Foreign key may already exist on re-run.
                }
            });
        }

        // Backfill catalog fields from linked checklist items.
        if (Schema::hasTable('checklist_items')) {
            $rows = DB::table('upload_types')
                ->whereNotNull('checklist_item_id')
                ->get(['id', 'checklist_item_id']);

            foreach ($rows as $row) {
                $item = DB::table('checklist_items')->where('id', $row->checklist_item_id)->first();
                if (! $item) {
                    continue;
                }

                $positionIds = null;
                if ($item->position_ids !== null) {
                    $decoded = json_decode((string) $item->position_ids, true);
                    $positionIds = is_array($decoded) ? $decoded : null;
                }

                $appliesToAll = $positionIds === null;

                DB::table('upload_types')->where('id', $row->id)->update([
                    'doc_type_id' => $item->doc_type_id,
                    'sort_order' => (int) ($item->order ?? 0),
                    'applies_to_all_positions' => $appliesToAll,
                ]);

                // Empty JSON arrays mean "no positions" — keep false for applies_to_all.
                if (is_array($positionIds) && $positionIds !== []) {
                    foreach ($positionIds as $positionId) {
                        $positionId = (int) $positionId;
                        if ($positionId <= 0) {
                            continue;
                        }
                        $exists = DB::table('position_upload_type_requirements')
                            ->where('position_id', $positionId)
                            ->where('upload_type_id', $row->id)
                            ->exists();
                        if (! $exists && Schema::hasTable('positions') && DB::table('positions')->where('id', $positionId)->exists()) {
                            DB::table('position_upload_type_requirements')->insert([
                                'position_id' => $positionId,
                                'upload_type_id' => $row->id,
                                'is_required' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('upload_types', function (Blueprint $table) {
            if (Schema::hasColumn('upload_types', 'doc_type_id')) {
                try {
                    $table->dropForeign(['doc_type_id']);
                } catch (\Throwable) {
                }
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('upload_types', 'doc_type_id') ? 'doc_type_id' : null,
                Schema::hasColumn('upload_types', 'sort_order') ? 'sort_order' : null,
                Schema::hasColumn('upload_types', 'applies_to_all_positions') ? 'applies_to_all_positions' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
