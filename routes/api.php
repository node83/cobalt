<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (App $app) {
    $app->group('/api/v1', function (RouteCollectorProxyInterface $app) {
    });
};
