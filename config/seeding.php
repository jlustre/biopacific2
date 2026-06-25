<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seed demo / transactional data
    |--------------------------------------------------------------------------
    |
    | When false, `php artisan db:seed` runs StructuralSeeder only (reference
    | data plus the super-admin account). Demo users, facilities, and sample
    | content are skipped.
    |
    | When true, DemoDataSeeder also runs (facilities, users, employees, sample
    | news, uploads, etc.). Set to false on production.
    |
    | Defaults to false when APP_ENV=production, true otherwise.
    |
    */

    'demo_data' => filter_var(
        env('SEED_DEMO_DATA', env('APP_ENV', 'local') !== 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

];
