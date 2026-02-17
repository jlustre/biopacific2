<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add department_id column (nullable for now)
        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('description');
        });

        // Step 2: Migrate existing data - map department name to department_id
        $positions = DB::table('positions')->whereNotNull('department')->get();
        foreach ($positions as $position) {
            $department = DB::table('departments')->where('name', $position->department)->first();
            if ($department) {
                DB::table('positions')
                    ->where('id', $position->id)
                    ->update(['department_id' => $department->id]);
            }
        }

        // Step 3: Make department_id required and add foreign key
        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });

        // Step 4: Drop department column
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add department column back
        Schema::table('positions', function (Blueprint $table) {
            $table->string('department')->nullable()->after('description');
        });

        // Step 2: Restore department from department_id
        $positions = DB::table('positions')->whereNotNull('department_id')->get();
        foreach ($positions as $position) {
            $department = DB::table('departments')->where('id', $position->department_id)->first();
            if ($department) {
                DB::table('positions')
                    ->where('id', $position->id)
                    ->update(['department' => $department->name]);
            }
        }

        // Step 3: Drop foreign key and department_id column
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
