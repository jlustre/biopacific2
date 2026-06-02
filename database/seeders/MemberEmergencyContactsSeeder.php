<?php

namespace Database\Seeders;

use App\Models\MemberEmergencyContact;
use App\Models\User;
use Illuminate\Database\Seeder;

class MemberEmergencyContactsSeeder extends Seeder
{
    public function run(): void
    {
        $contactsByEmail = [
            'facilityadmin@biopacific.com' => [
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'relationship' => 'Spouse',
                    'phone' => '4085551234',
                    'email' => 'jane.doe@example.com',
                    'address1' => '1401 Almaden Way',
                    'city' => 'San Jose',
                    'state' => 'CA',
                    'zip' => '95125',
                    'is_primary' => true,
                    'sort_order' => 0,
                ],
                [
                    'first_name' => 'Mary',
                    'last_name' => 'Smith',
                    'relationship' => 'Sister',
                    'phone' => '4085559876',
                    'email' => 'mary.smith@example.com',
                    'is_primary' => false,
                    'sort_order' => 1,
                ],
            ],
            'facilitydsd@biopacific.com' => [
                [
                    'first_name' => 'Robert',
                    'last_name' => 'Randell',
                    'relationship' => 'Spouse',
                    'phone' => '5105552200',
                    'email' => 'robert.randell@example.com',
                    'is_primary' => true,
                    'sort_order' => 0,
                ],
            ],
        ];

        foreach ($contactsByEmail as $email => $contacts) {
            $user = User::query()->where('email', $email)->first();
            if (!$user) {
                continue;
            }

            MemberEmergencyContact::query()->where('user_id', $user->id)->delete();

            foreach ($contacts as $contact) {
                MemberEmergencyContact::create(array_merge($contact, ['user_id' => $user->id]));
            }
        }
    }
}
