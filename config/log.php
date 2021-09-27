<?php
declare(strict_types=1);

use App\Core;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    'stack' => Core::env('LOG_STACK', 'default'),

    'stacks' => [
        'default' => ['stderr', 'file'],
    ],

    'handlers' => [
        'stderr' => (object)[
            'handler' => StreamHandler::class,
            'params' => [
                'stream' => 'php://stderr',
                'level' => Logger::NOTICE,
                'bubble' => true,
                'filePermission' => 0644,
                'useLocking' => false,
            ],
        ],

        'file' => (object)[
            'handler' => RotatingFileHandler::class,
            'params' => [
                'filename' => Core::path('storage/logs/' . Core::env('LOG_FILE', 'cobalt.log')),
                'maxFiles' => 7,
                'level' => Logger::INFO,
                'bubble' => true,
                'filePermission' => 0644,
                'useLocking' => false,
            ],
        ],
    ],
];
