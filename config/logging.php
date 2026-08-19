<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WAHA log channels
    |--------------------------------------------------------------------------
    |
    | The package writes to two dedicated channels so request/response noise
    | ("waha") stays separate from errors ("wahaError"). These are merged into
    | the host application's `logging.channels`; override them there if needed.
    |
    */
    'waha' => [
        'driver'               => 'daily',
        'path'                 => storage_path('logs/waha/waha.log'),
        'level'                => env('LOG_LEVEL', 'debug'),
        'days'                 => env('LOG_DAILY_DAYS', 14),
        'replace_placeholders' => true,
        'permission'           => 0664,
    ],

    'wahaError' => [
        'driver'               => 'daily',
        'path'                 => storage_path('logs/waha/waha_error.log'),
        'level'                => env('LOG_LEVEL', 'debug'),
        'days'                 => env('LOG_DAILY_DAYS', 14),
        'replace_placeholders' => true,
        'permission'           => 0664,
    ],
];
