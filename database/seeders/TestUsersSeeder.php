<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\SeedsUserEmployeeRecords;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'email' => 'rdhr@biopacific.com',
                'name' => 'Liszel Justice',
                'facility_id' => 99,
                'roles' => ['rdhr'],
                'employee' => [
                    'employee_num' => 'USR000101',
                    'facility_id' => 99,
                    'position_title' => 'Administrator',
                    'first_name' => 'Liszel',
                    'last_name' => 'Justice',
                    'middle_name' => 'M',
                    'gender' => 'F',
                    'dob' => '1988-01-15',
                    'original_hire_dt' => '2019-02-01',
                    'is_union' => false,
                    'phone_number' => '555-010-9101',
                    'address1' => '1100 Regional Dr',
                    'city' => 'San Jose',
                    'state' => 'CA',
                    'zip' => '95113',
                ],
            ],
            [
                'email' => 'facilityadmin@biopacific.com',
                'name' => 'Michael Monroe',
                'facility_id' => 14,
                'roles' => ['facility-admin'],
                'employee' => [
                    'employee_num' => 'USR000102',
                    'facility_id' => 14,
                    'position_title' => 'Administrator',
                    'first_name' => 'Michael',
                    'last_name' => 'Monroe',
                    'middle_name' => 'T',
                    'gender' => 'M',
                    'dob' => '1982-05-22',
                    'original_hire_dt' => '2018-06-15',
                    'is_union' => false,
                    'phone_number' => '555-010-9102',
                    'address1' => '1401 Almaden Way',
                    'city' => 'San Jose',
                    'state' => 'CA',
                    'zip' => '95125',
                ],
            ],
            [
                'email' => 'facilitydsd@biopacific.com',
                'name' => 'Chimere Randell',
                'facility_id' => 17,
                'roles' => ['facility-dsd'],
                'employee' => [
                    'employee_num' => 'USR000103',
                    'facility_id' => 17,
                    'position_title' => 'Staff Development Coordinator',
                    'first_name' => 'Chimere',
                    'last_name' => 'Randell',
                    'middle_name' => 'L',
                    'gender' => 'F',
                    'dob' => '1985-08-09',
                    'original_hire_dt' => '2020-03-01',
                    'is_union' => false,
                    'phone_number' => '555-010-9103',
                    'address1' => '1700 Education Ln',
                    'city' => 'Fresno',
                    'state' => 'CA',
                    'zip' => '93722',
                ],
            ],
            [
                'email' => 'don@biopacific.com',
                'name' => 'Melijoy Adan',
                'facility_id' => 14,
                'roles' => ['don'],
                'employee' => [
                    'employee_num' => 'USR000104',
                    'facility_id' => 14,
                    'position_title' => 'Director of Nursing',
                    'first_name' => 'Melijoy',
                    'last_name' => 'Adan',
                    'middle_name' => 'P',
                    'gender' => 'F',
                    'dob' => '1979-11-03',
                    'original_hire_dt' => '2017-09-10',
                    'is_union' => false,
                    'phone_number' => '555-010-9104',
                    'address1' => '1715 Nursing Ave',
                    'city' => 'Fresno',
                    'state' => 'CA',
                    'zip' => '93721',
                ],
            ],
            [
                'email' => 'ssd@biopacific.com',
                'name' => 'Cynthia Prasad',
                'facility_id' => 17,
                'roles' => ['ssd'],
                'employee' => [
                    'employee_num' => 'USR000105',
                    'facility_id' => 17,
                    'position_title' => 'Social Services Director',
                    'first_name' => 'Cynthia',
                    'last_name' => 'Prasad',
                    'middle_name' => 'R',
                    'gender' => 'F',
                    'dob' => '1987-04-14',
                    'original_hire_dt' => '2021-01-11',
                    'is_union' => false,
                    'phone_number' => '555-010-9105',
                    'address1' => '1412 Community St',
                    'city' => 'San Jose',
                    'state' => 'CA',
                    'zip' => '95126',
                ],
            ],
            [
                'email' => 'activitiesdirector@biopacific.com',
                'name' => 'John Lastimosa',
                'facility_id' => 14,
                'roles' => ['activities-director'],
                'employee' => [
                    'employee_num' => 'USR000106',
                    'facility_id' => 14,
                    'position_title' => 'Activities Director',
                    'first_name' => 'John',
                    'last_name' => 'Lastimosa',
                    'middle_name' => 'P',
                    'gender' => 'M',
                    'dob' => '1990-12-19',
                    'original_hire_dt' => '2022-07-18',
                    'is_union' => false,
                    'phone_number' => '555-010-9106',
                    'address1' => '1420 Recreation Blvd',
                    'city' => 'San Jose',
                    'state' => 'CA',
                    'zip' => '95128',
                ],
            ],
            [
                'email' => 'facilityeditor@biopacific.com',
                'name' => 'Facility Editor',
                'facility_id' => 1,
                'roles' => ['facility-editor'],
                'employee' => [
                    'employee_num' => 'USR000107',
                    'facility_id' => 1,
                    'position_title' => 'Receptionist',
                    'first_name' => 'Facility',
                    'last_name' => 'Editor',
                    'middle_name' => null,
                    'gender' => 'N',
                    'dob' => '1992-06-30',
                    'original_hire_dt' => '2023-02-01',
                    'is_union' => false,
                    'phone_number' => '555-010-9107',
                    'address1' => '100 Front Desk Ct',
                    'city' => 'Los Angeles',
                    'state' => 'CA',
                    'zip' => '90001',
                ],
            ],
            [
                'email' => 'regularuser@biopacific.com',
                'name' => 'John Doe',
                'facility_id' => 1,
                'roles' => ['regular-user'],
                'employee' => [
                    'employee_num' => 'USR000108',
                    'facility_id' => 1,
                    'position_title' => 'Certified Nursing Assistant',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'middle_name' => 'Q',
                    'gender' => 'M',
                    'dob' => '1994-10-05',
                    'original_hire_dt' => '2024-01-08',
                    'is_union' => true,
                    'phone_number' => '555-010-9108',
                    'address1' => '101 Employee Way',
                    'city' => 'Los Angeles',
                    'state' => 'CA',
                    'zip' => '90002',
                ],
            ],
        ];

        foreach ($profiles as $profile) {
            $user = User::query()->firstOrNew(['email' => $profile['email']]);
            $user->name = $profile['name'];
            $user->facility_id = $profile['facility_id'];
            $user->email_verified_at = now();

            if (!$user->exists || Hash::needsRehash((string) $user->password)) {
                $user->password = Hash::make('password');
            }

            $user->save();
            $user->syncRoles($profile['roles']);

            SeedsUserEmployeeRecords::seed($user, Arr::get($profile, 'employee', []));
        }
    }
}
