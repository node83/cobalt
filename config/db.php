<?php
declare(strict_types=1);

use App\Core;

return [
    'dsn' => Core::env('DB_DSN'),
    'user' => Core::env('DB_USER'),
    'password' => Core::env('DB_PASSWORD'),
];
