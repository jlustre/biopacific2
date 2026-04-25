<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SelectOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch option types by name for mapping
        $optionTypes = DB::table('optionstypes')->pluck('id', 'name');

        $selectOptions = [];
        // Marital Status options
        $maritalStatus = ['Single', 'Married', 'Separated', 'Divorced', 'Widowed'];
        foreach ($maritalStatus as $i => $value) {
            $selectOptions[] = [
                'type_id' => $optionTypes['Marital Status'] ?? null,
                'name' => $value,
                'value' => $value,
                'isActive' => 1,
                'sort_order' => $i + 1
            ];
        }

        // Ethnic Group options
        $ethnicGroup = [
            'Hispanic/Latino',
            'Asian-Pacific Islander',
            'Amer. Indian/Alaskan Native',
            'Black/African American',
            'White'
        ];
        foreach ($ethnicGroup as $i => $value) {
            $selectOptions[] = [
                'type_id' => $optionTypes['Ethnic Group'] ?? null,
                'name' => $value,
                'value' => $value,
                'isActive' => 1,
                'sort_order' => $i + 1
            ];
        }

        // Military Status options
        $militaryStatus = [
            'NO Military Service',
            'Not a Veteran',
            'Not a Vietnam-Era Veteran',
            'Not Indicated',
            'Other Eligible US Veteran',
            'Post-Vietnam Era Veteran',
            'Pre-Vietnam Era Veteran',
            'Veteran (VA Ineligible)',
            'Vietnam & Other Eligible Veteran',
            'Vietnam Era Veteran'
        ];
        foreach ($militaryStatus as $i => $value) {
            $selectOptions[] = [
                'type_id' => $optionTypes['Military Status'] ?? null,
                'name' => $value,
                'value' => $value,
                'isActive' => 1,
                'sort_order' => $i + 1
            ];
        }

        // US Citizenship Status options
        $citizenshipStatus = [
            'US Citizen',
            'Alien Permanent',
            'Alien Temporary',
            'Work Visa',
            'Naturalized Citizen',
            'Natural Born Citizen'
        ];
        foreach ($citizenshipStatus as $i => $value) {
            $selectOptions[] = [
                'type_id' => $optionTypes['Citizenship Status'] ?? null,
                'name' => $value,
                'value' => $value,
                'isActive' => 1,
                'sort_order' => $i + 1
            ];
        }

        // Hiring Action
        $hiringAction = ['New Hire', 'Rehire'];
        foreach ($hiringAction as $i => $value) {
            $selectOptions[] = [
                'type_id' => $optionTypes['Hiring Action'] ?? null,
                'name' => $value,
                'value' => $value,
                'isActive' => 1,
                'sort_order' => $i + 1
            ];
        }

        // Hourly Status
        $hourlyStatus = ['Full-time', 'Part-time', 'PRN'];
        foreach ($hourlyStatus as $i => $value) {
            $selectOptions[] = [
                'type_id' => $optionTypes['Hourly Status'] ?? null,
                'name' => $value,
                'value' => $value,
                'isActive' => 1,
                'sort_order' => $i + 1
            ];
        }

        // Filter out any with null type_id (in case option type is missing)
        $selectOptions = array_filter($selectOptions, function($opt) {
            return !is_null($opt['type_id']);
        });

        DB::table('selectoptions')->insert($selectOptions);
    }
}
