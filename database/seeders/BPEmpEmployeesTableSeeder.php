<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class BPEmpEmployeesTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $employees = [];
        $empIds = [];
        for ($i = 1; $i <= 20; $i++) {
            $empId = 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $empIds[] = $empId;
            $employees[] = [
                'employee_num' => $empId,
                'ssn' => $faker->unique()->numerify('###-##-####'),
                'first_name' => $faker->firstName,
                'middle_name' => null,
                'last_name' => $faker->lastName,
                'gender' => $faker->randomElement(['M', 'F', 'O', 'N']),
                'assignment_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('bp_employees')->insert($employees);

        // After assignments are seeded, update bp_employees with assignment_id
        $assignments = DB::table('bp_emp_assignments')->orderBy('assign_id')->get();
        foreach ($assignments as $assignment) {
            DB::table('bp_employees')->where('employee_num', $assignment->employee_num)->update([
                'assignment_id' => $assignment->assign_id,
            ]);
        }

    }
}
