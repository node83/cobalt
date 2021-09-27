<?php
declare(strict_types=1);

use App\Controllers\{HomeController, IndexController, LoginController, LogoutPassword, RegisterController};
use App\Core;
use App\Middleware\{AuthMiddleware, BasicAuthMiddleware, CsrfMiddleware, GuestMiddleware, SessionMiddleware};
use App\Providers\DatabaseAuthorizationProvider;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (App $app) {
    /** Web Routes */
    $app->group('', function (RouteCollectorProxyInterface $app) {
        /** Guest Only Routes */
        $app->group('', function (RouteCollectorProxyInterface $app) {
            $app->get('/', IndexController::class)->setName('index');
            $app->map(['GET', 'POST'], '/login', LoginController::class)->setName('login');
            $app->map(['GET', 'POST'], '/register', RegisterController::class)->setName('register');
            // ...
        })->add(new GuestMiddleware());

        /** Guest & Auth Routes */
        $app->post('/logout', LogoutPassword::class)->setName('logout');

        /** Auth Only Routes */
        $app->group('', function (RouteCollectorProxyInterface $app) {
            $app->get('/home', HomeController::class)->setName('home');
            // ...
        })->add(new AuthMiddleware());
    })->add(new CsrfMiddleware())->add(new SessionMiddleware());

    /** Health Check */
    $app->get('/ping', function (ResponseInterface $response): ResponseInterface {
        $response->getBody()->write('pong');
        return $response->withHeader('Content-Type', 'text/plain');
    });

    /** API Routes */
    $app->group('/api', function (RouteCollectorProxyInterface $app) {
        /** Authentication validator */
        $app->get('/auth/check', function(ResponseInterface $response) {
            $response->getBody()->write(json_encode(['status' => 'success'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
        });

        /** API Endpoints */
        $app->group('/v1', function (RouteCollectorProxyInterface $app) {
            // ...
        });
    })->add(new BasicAuthMiddleware(Core::get(DatabaseAuthorizationProvider::class)));
};
