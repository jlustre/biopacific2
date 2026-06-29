<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChecklistItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/checklist_items.php');
        if (! is_file($path)) {
            throw new \RuntimeException('Missing checklist items seed data file: database/seeders/data/checklist_items.php');
        }

        /** @var list<array<string, mixed>> $items */
        $items = require $path;

        // MySQL rejects TRUNCATE when uploads/upload_types FK-reference checklist_items.
        Schema::disableForeignKeyConstraints();

        DB::table('uploads')->update(['checklist_item_id' => null]);
        DB::table('upload_types')->update([
            'checklist_item_id' => null,
            'checklist_section' => null,
        ]);
        DB::table('checklist_items')->truncate();

        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();
        $order = 1;

        foreach ($items as $item) {
            $positionIds = $item['position_ids'] ?? null;
            if ($positionIds === [] || $positionIds === '') {
                $positionIds = null;
            }

            DB::table('checklist_items')->insert([
                'name' => $item['name'],
                'section' => $item['section'],
                'doc_type_id' => $item['doc_type_id'],
                'isExpiring' => (bool) ($item['isExpiring'] ?? false),
                'is_required' => (bool) ($item['is_required'] ?? true),
                'is_license_or_certification' => (bool) ($item['is_license_or_certification'] ?? false),
                'position_ids' => $positionIds !== null ? json_encode(array_values((array) $positionIds)) : null,
                'order' => (int) ($item['order'] ?? $order),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $order++;
        }
    }
}
