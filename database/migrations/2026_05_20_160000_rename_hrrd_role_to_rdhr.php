<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'hrrd')
            ->update(['name' => 'rdhr']);

        $this->renameRoleInJsonColumns('hrrd', 'rdhr');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'rdhr')
            ->update(['name' => 'hrrd']);

        $this->renameRoleInJsonColumns('rdhr', 'hrrd');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function renameRoleInJsonColumns(string $from, string $to): void
    {
        if (!DB::getSchemaBuilder()->hasTable('reports')) {
            return;
        }

        if (DB::getSchemaBuilder()->hasColumn('reports', 'visible_roles')) {
            DB::table('reports')->orderBy('id')->chunkById(100, function ($rows) use ($from, $to) {
                foreach ($rows as $row) {
                    $roles = json_decode($row->visible_roles ?? '[]', true);
                    if (!is_array($roles)) {
                        continue;
                    }
                    $updated = false;
                    foreach ($roles as $i => $role) {
                        if ($role === $from) {
                            $roles[$i] = $to;
                            $updated = true;
                        }
                    }
                    if ($updated) {
                        DB::table('reports')->where('id', $row->id)->update([
                            'visible_roles' => json_encode(array_values($roles)),
                        ]);
                    }
                }
            });
        }

        if (!DB::getSchemaBuilder()->hasTable('scheduled_reports')) {
            return;
        }

        if (DB::getSchemaBuilder()->hasColumn('scheduled_reports', 'notify_roles')) {
            DB::table('scheduled_reports')->orderBy('id')->chunkById(100, function ($rows) use ($from, $to) {
                foreach ($rows as $row) {
                    $roles = json_decode($row->notify_roles ?? '[]', true);
                    if (!is_array($roles)) {
                        continue;
                    }
                    $updated = false;
                    foreach ($roles as $i => $role) {
                        if ($role === $from) {
                            $roles[$i] = $to;
                            $updated = true;
                        }
                    }
                    if ($updated) {
                        DB::table('scheduled_reports')->where('id', $row->id)->update([
                            'notify_roles' => json_encode(array_values($roles)),
                        ]);
                    }
                }
            });
        }
    }
};
