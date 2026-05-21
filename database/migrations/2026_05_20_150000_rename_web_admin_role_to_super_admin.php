<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'web-admin')
            ->update(['name' => 'super-admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'super-admin')
            ->update(['name' => 'web-admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
