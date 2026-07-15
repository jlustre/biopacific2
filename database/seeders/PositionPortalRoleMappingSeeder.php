<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\PositionPortalRoleMapping;
use App\Support\PositionPortalRoleMappingService;
use Illuminate\Database\Seeder;

class PositionPortalRoleMappingSeeder extends Seeder
{
    public function run(): void
    {
        $exportedDataPath = database_path('seeders/data/position_portal_role_mappings.php');
        if (is_file($exportedDataPath)) {
            $mappings = require $exportedDataPath;

            if (is_array($mappings)) {
                $positionsByTitle = Position::query()->pluck('id', 'title');
                $seeded = 0;

                foreach ($mappings as $mapping) {
                    $positionId = $positionsByTitle->get((string) ($mapping['position_title'] ?? ''));
                    if (! $positionId || empty($mapping['role_name'])) {
                        continue;
                    }

                    PositionPortalRoleMapping::query()->updateOrCreate(
                        ['position_id' => (int) $positionId],
                        [
                            'role_name' => (string) $mapping['role_name'],
                            'is_active' => (bool) ($mapping['is_active'] ?? true),
                        ]
                    );
                    $seeded++;
                }

                $this->command?->info("Seeded {$seeded} exported position portal role mapping(s).");

                return;
            }
        }

        app(PositionPortalRoleMappingService::class)->syncDefaultMappings();
    }
}
