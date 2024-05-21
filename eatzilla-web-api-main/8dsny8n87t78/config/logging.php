<?php

return [
    'channels' =>  [
        'cloudwatch' => [
                'name' => env('CLOUDWATCH_LOG_NAME', ''),
                'region' => env('CLOUDWATCH_LOG_REGION', ''),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID', ''),
                    'secret' => env('AWS_SECRET_ACCESS_KEY', '')
                ],
                'stream_name' => env('CLOUDWATCH_LOG_STREAM_NAME', 'laravel_app'),
                'retention' => env('CLOUDWATCH_LOG_RETENTION_DAYS', 14),
                'group_name' => env('CLOUDWATCH_LOG_GROUP_NAME', 'laravel_app'),
                'version' => env('CLOUDWATCH_LOG_VERSION', 'latest'),
                'formatter' => \Monolog\Formatter\JsonFormatter::class,
                'disabled' => env('DISABLE_CLOUDWATCH_LOG', false),
            ],
        ]
];