<?php
declare(strict_types=1);

use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function () {
        return new Logger($_ENV['APP_NAME']);
    },
];
