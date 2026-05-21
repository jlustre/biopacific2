<?php

return [
    'global_facility_id' => 99,

    'target_tables' => [
        'bp_emp_checklists',
        'bp_emp_job_data',
        'bp_employees',
        'bp_emp_phones',
        'bp_emp_addresses',
        'bp_emp_compensation',
        'bp_emp_health_screenings',
        'bp_emp_credentials',
        'bp_emp_tax_data',
    ],

    'permissions' => [
        'create' => 'create import mapping presets',
        'use' => 'use import mapping presets',
    ],
];
