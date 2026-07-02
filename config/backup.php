<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Storage
    |--------------------------------------------------------------------------
    */
    'disk' => env('BACKUP_DISK', 'local'),
    'directory' => env('BACKUP_DIRECTORY', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Remote / off-site mirror (e.g. S3)
    |--------------------------------------------------------------------------
    */
    'remote_mirror_enabled' => env('BACKUP_REMOTE_MIRROR_ENABLED', false),
    'remote_disk' => env('BACKUP_REMOTE_DISK', 's3'),
    'remote_directory' => env('BACKUP_REMOTE_DIRECTORY', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Scheduled backups
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', false),
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'),
        'timezone' => env('BACKUP_SCHEDULE_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
        'type' => env('BACKUP_SCHEDULE_TYPE', 'full'),
        'notes' => env('BACKUP_SCHEDULE_NOTES', 'Scheduled nightly backup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Safety
    |--------------------------------------------------------------------------
    */
    'pre_restore_backup' => env('BACKUP_PRE_RESTORE', true),
    'max_upload_size_mb' => (int) env('BACKUP_MAX_UPLOAD_MB', 512),

    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    | When true, backups/restores run immediately in the web request instead of
    | waiting for a queue worker (recommended for local Laragon setups).
    */
    'process_immediately' => env('BACKUP_PROCESS_IMMEDIATELY', true),

    /*
    |--------------------------------------------------------------------------
    | Backup destinations
    |--------------------------------------------------------------------------
    | local  — default application storage disk
    | cloud  — direct write to remote_disk (enable + configure S3/etc.)
    | external — mounted USB/external path on the server (BACKUP_EXTERNAL_PATH)
    */
    'destinations' => [
        'cloud' => [
            'enabled' => env('BACKUP_CLOUD_DESTINATION_ENABLED', false),
        ],
        'external' => [
            'enabled' => env('BACKUP_EXTERNAL_ENABLED', false),
        ],
    ],

    'external_path' => env('BACKUP_EXTERNAL_PATH'),
    'external_directory' => env('BACKUP_EXTERNAL_DIRECTORY', 'biopacific-backups'),

    /*
    |--------------------------------------------------------------------------
    | Restore compatibility
    |--------------------------------------------------------------------------
    | Which backup types may be used for each restore mode.
    */
    'restore_compatibility' => [
        'full' => ['full'],
        'structural' => ['full', 'structural'],
        'transactional' => ['full', 'transactional'],
        'files' => ['full', 'files'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application metadata included in manifests
    |--------------------------------------------------------------------------
    */
    'app_version' => env('APP_VERSION', '1.0.0'),

];
