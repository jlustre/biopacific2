<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_email_mappings', function (Blueprint $table) {
            $table->string('contact_role', 64)->nullable()->after('category');
            $table->foreignId('user_id')->nullable()->after('contact_role')->constrained('users')->nullOnDelete();
            $table->boolean('on_vacation')->default(false)->after('is_active');
            $table->date('vacation_starts_at')->nullable()->after('on_vacation');
            $table->date('vacation_ends_at')->nullable()->after('vacation_starts_at');
            $table->index(['category', 'contact_role']);
        });

        Schema::table('employee_email_mappings', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE employee_email_mappings MODIFY facility_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE employee_email_mappings ALTER COLUMN facility_id DROP NOT NULL');
        }

        Schema::table('employee_email_mappings', function (Blueprint $table) {
            $table->foreign('facility_id')->references('id')->on('facilities')->nullOnDelete();
        });

        if (Schema::hasTable('portal_help_recipients')) {
            $rows = DB::table('portal_help_recipients')->orderBy('id')->get();
            foreach ($rows as $row) {
                $role = $row->channel === 'hr_inquiry'
                    ? ($row->responsibility === 'primary' ? 'hr_primary' : 'hr_secondary')
                    : ($row->responsibility === 'primary' ? 'tech_primary' : 'tech_secondary');

                $email = $row->email;
                if (! filled($email) && $row->user_id) {
                    $email = DB::table('users')->where('id', $row->user_id)->value('email');
                }

                DB::table('employee_email_mappings')->insert([
                    'facility_id' => null,
                    'category' => $row->channel,
                    'contact_role' => $role,
                    'user_id' => $row->user_id,
                    'employee_name' => $row->name ?: ($email ?: 'Recipient'),
                    'employee_email' => $email ?: 'unknown@example.com',
                    'title' => $row->responsibility === 'primary' ? 'Primary contact' : 'Secondary contact',
                    'is_primary' => $row->responsibility === 'primary',
                    'is_active' => (bool) $row->is_active,
                    'on_vacation' => (bool) $row->on_vacation,
                    'vacation_starts_at' => $row->vacation_starts_at,
                    'vacation_ends_at' => $row->vacation_ends_at,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('employee_email_mappings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['category', 'contact_role']);
            $table->dropColumn([
                'contact_role',
                'user_id',
                'on_vacation',
                'vacation_starts_at',
                'vacation_ends_at',
            ]);
        });
    }
};
