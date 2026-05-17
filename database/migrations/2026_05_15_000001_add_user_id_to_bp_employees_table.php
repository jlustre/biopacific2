<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('bp_employees', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('bp_employees', 'user_id') && Schema::hasColumn('bp_employees', 'email')) {
            DB::table('bp_employees')
                ->whereNull('user_id')
                ->whereNotNull('email')
                ->orderBy('id')
                ->chunkById(100, function ($employees) {
                    foreach ($employees as $employee) {
                        $userId = DB::table('users')
                            ->where('email', $employee->email)
                            ->value('id');

                        if ($userId) {
                            DB::table('bp_employees')
                                ->where('id', $employee->id)
                                ->update(['user_id' => $userId]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            if (Schema::hasColumn('bp_employees', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
