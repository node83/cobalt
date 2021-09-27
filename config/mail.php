<?php
declare(strict_types=1);

use App\Core;

return [
    'dsn' => Core::env('MAIL_DSN', 'http://localhost:1025'),
];
