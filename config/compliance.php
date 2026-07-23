<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Due-before offset (days)
    |--------------------------------------------------------------------------
    |
    | Training, competencies, performance appraisals, and documents with an
    | expiration are due this many days before the hire anniversary or the
    | document expiration date.
    |
    */
    'due_before_days' => (int) env('COMPLIANCE_DUE_BEFORE_DAYS', 30),
];
