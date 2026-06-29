<?php

namespace Database\Seeders;

use App\Support\PositionPortalRoleMappingService;
use Illuminate\Database\Seeder;

class PositionPortalRoleMappingSeeder extends Seeder
{
    public function run(): void
    {
        app(PositionPortalRoleMappingService::class)->syncDefaultMappings();
    }
}
