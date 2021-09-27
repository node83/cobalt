<?php
declare(strict_types=1);

use App\Core;

return [
    'debug' => filter_var(Core::env('APP_DEBUG', 0), FILTER_VALIDATE_BOOL),
    'env' => array_reverse(preg_grep('`^' . preg_quote(Core::env('APP_ENV') ?? 'production') . '$`i', [
            'local',
            'development',
            'testing',
            'staging',
            'production',
        ]))[0] ?? 'production',
    'key' => Core::env('APP_KEY'),
    'name' => Core::env('APP_NAME', 'Cobalt'),
];
