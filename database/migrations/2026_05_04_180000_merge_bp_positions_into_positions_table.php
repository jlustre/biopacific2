<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'legacy_position_id')) {
                $table->unsignedBigInteger('legacy_position_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('positions', 'position_code')) {
                $table->string('position_code')->nullable()->after('title');
            }
            if (!Schema::hasColumn('positions', 'has_supervisor_role')) {
                $table->boolean('has_supervisor_role')->default(false)->after('supervisor_role');
            }
            if (!Schema::hasColumn('positions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('has_supervisor_role');
            }
        });

        DB::table('positions')
            ->update(['has_supervisor_role' => DB::raw('supervisor_role')]);

        $departmentMap = $this->departmentMap();
        $positionsByTitle = DB::table('positions')
            ->select('id', 'title')
            ->get()
            ->mapWithKeys(fn ($position) => [mb_strtolower(trim($position->title)) => $position->id])
            ->all();

        if (Schema::hasTable('bp_positions')) {
            $legacyPositions = DB::table('bp_positions')->orderBy('position_id')->get();

            foreach ($legacyPositions as $legacyPosition) {
                $matchedId = $positionsByTitle[mb_strtolower(trim($legacyPosition->position_title))] ?? null;

                $payload = [
                    'title' => $legacyPosition->position_title,
                    'position_code' => $legacyPosition->position_code,
                    'description' => $legacyPosition->description,
                    'supervisor_role' => (bool) $legacyPosition->has_supervisor_role,
                    'has_supervisor_role' => (bool) $legacyPosition->has_supervisor_role,
                    'is_active' => (bool) $legacyPosition->is_active,
                    'legacy_position_id' => $legacyPosition->position_id,
                    'updated_at' => now(),
                ];

                if ($matchedId) {
                    DB::table('positions')
                        ->where('id', $matchedId)
                        ->update(array_filter($payload, static fn ($value) => $value !== null));
                    continue;
                }

                $departmentId = $departmentMap[$legacyPosition->dept_code] ?? $departmentMap['ADMIN'] ?? DB::table('departments')->min('id');

                $newId = DB::table('positions')->insertGetId($payload + [
                    'department_id' => $departmentId,
                    'created_at' => now(),
                ]);

                $positionsByTitle[mb_strtolower(trim($legacyPosition->position_title))] = $newId;
            }

            $idMap = DB::table('positions')
                ->whereNotNull('legacy_position_id')
                ->pluck('id', 'legacy_position_id')
                ->all();

            if (Schema::hasTable('bp_emp_assignments')) {
                DB::table('bp_emp_assignments')->orderBy('assign_id')->get(['assign_id', 'job_code_id', 'reports_to'])->each(function ($assignment) use ($idMap) {
                    $jobCodeId = $idMap[$assignment->job_code_id] ?? $assignment->job_code_id;
                    $reportsTo = $idMap[$assignment->reports_to] ?? $assignment->reports_to;

                    DB::table('bp_emp_assignments')
                        ->where('assign_id', $assignment->assign_id)
                        ->update([
                            'job_code_id' => $jobCodeId,
                            'reports_to' => $reportsTo,
                        ]);
                });
            }

            if (Schema::hasTable('checklist_items')) {
                DB::table('checklist_items')->orderBy('id')->get(['id', 'position_ids'])->each(function ($item) use ($idMap) {
                    if (empty($item->position_ids)) {
                        return;
                    }

                    $positionIds = is_array($item->position_ids)
                        ? $item->position_ids
                        : json_decode($item->position_ids, true);

                    if (!is_array($positionIds)) {
                        return;
                    }

                    $mapped = collect($positionIds)
                        ->map(function ($positionId) use ($idMap) {
                            $positionId = (int) $positionId;
                            return $idMap[$positionId] ?? DB::table('positions')->where('id', $positionId)->value('id');
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    DB::table('checklist_items')
                        ->where('id', $item->id)
                        ->update(['position_ids' => empty($mapped) ? null : json_encode($mapped)]);
                });
            }

            Schema::dropIfExists('bp_positions');
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->unique('legacy_position_id');
            $table->unique('position_code');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bp_positions')) {
            Schema::create('bp_positions', function (Blueprint $table) {
                $table->id('position_id');
                $table->string('position_code')->unique();
                $table->string('position_title');
                $table->string('dept_code');
                $table->boolean('has_supervisor_role')->default(false);
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('positions')
                ->whereNotNull('legacy_position_id')
                ->orderBy('legacy_position_id')
                ->get()
                ->each(function ($position) {
                    DB::table('bp_positions')->insert([
                        'position_id' => $position->legacy_position_id,
                        'position_code' => $position->position_code,
                        'position_title' => $position->title,
                        'dept_code' => 'ADMIN',
                        'has_supervisor_role' => (bool) $position->has_supervisor_role,
                        'description' => $position->description,
                        'is_active' => (bool) $position->is_active,
                        'created_at' => $position->created_at,
                        'updated_at' => $position->updated_at,
                    ]);
                });
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['legacy_position_id']);
            $table->dropUnique(['position_code']);
            $table->dropColumn([
                'legacy_position_id',
                'position_code',
                'has_supervisor_role',
                'is_active',
            ]);
        });
    }

    private function departmentMap(): array
    {
        $namesByCode = [
            'ADMIN' => 'Administration',
            'ADMIT' => 'Admissions',
            'NURS' => 'Nursing',
            'SOC' => 'Social Services',
            'ACT' => 'Activities',
            'DIET' => 'Dietary',
            'ENV' => 'Environmental Services',
            'MAINT' => 'Maintenance',
            'IT' => 'Corporate Information Technology',
            'FIN' => 'Corporate Finance',
            'QA' => 'Quality Assurance',
            'PHARM' => 'Pharmacy',
            'REHAB' => 'Rehabilitation Services',
            'SEC' => 'Security',
        ];

        return collect($namesByCode)
            ->mapWithKeys(function ($departmentName, $code) {
                return [$code => DB::table('departments')->where('name', $departmentName)->value('id')];
            })
            ->filter()
            ->all();
    }
};