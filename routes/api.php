<?php
declare(strict_types=1);

use App\Classes\Database;
use App\Core;
use App\Middleware\BasicAuthMiddleware;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (App $app) {
    $app->group('/api', function (RouteCollectorProxyInterface $app) {
        $app->get('/auth/check', function(ResponseInterface $response) {
            $response->getBody()->write(json_encode(['status' => 'success'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $app->group('/v1', function (RouteCollectorProxyInterface $app) {
            // API endpoints
        });
    })->add(new BasicAuthMiddleware(Core::get(Database::class)));
};
