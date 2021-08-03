<?php
declare(strict_types=1);

use App\Core;

return [
    'key' => Core::env('APP_KEY'),
    'name' => Core::env('APP_NAME', 'Cobalt'),
    'debug' => Core::env('APP_DEBUG', False),
];
