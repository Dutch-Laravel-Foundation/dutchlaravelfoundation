<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            // 'visibility' => 'public', // https://statamic.dev/assets#visibility
        ],

        'assets' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/assets'),
            'url' => '/assets/uploads/assets',
            'visibility' => 'public',
        ],

        'insights' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/insights'),
            'url' => '/assets/uploads/insights',
            'visibility' => 'public',
        ],

        'knowledge' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/knowledge'),
            'url' => '/assets/uploads/knowledge',
            'visibility' => 'public',
        ],

        'cases' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/cases'),
            'url' => '/assets/uploads/cases',
            'visibility' => 'public',
        ],

        'members' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/members'),
            'url' => '/assets/uploads/members',
            'visibility' => 'public',
        ],

        'board' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/board'),
            'url' => '/assets/uploads/board',
            'visibility' => 'public',
        ],

        'clients' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/clients'),
            'url' => '/assets/uploads/clients',
            'visibility' => 'public',
        ],

        'events' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/events'),
            'url' => '/assets/uploads/events',
            'visibility' => 'public',
        ],

        'socials' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/socials'),
            'url' => '/assets/uploads/socials',
            'visibility' => 'public',
        ],

        'globals' => [
            'driver' => 'local',
            'root' => public_path('assets/uploads/globals'),
            'url' => '/assets/uploads/globals',
            'visibility' => 'public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
