<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\IndexController;
use App\Controllers\LoginController;
use App\Controllers\LogoutPassword;
use App\Controllers\RegisterController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (App $app) {
    $app->group('', function (RouteCollectorProxyInterface $app) {

        $app->group('', function (RouteCollectorProxyInterface $app) {
            $app->get('/', IndexController::class)->setName('index');
            $app->map(['GET', 'POST'], '/login', LoginController::class)->setName('login');
            $app->map(['GET', 'POST'], '/register', RegisterController::class)->setName('register');
        })->add(new GuestMiddleware());

        $app->group('', function (RouteCollectorProxyInterface $app) {
            $app->post('/logout', LogoutPassword::class)->setName('logout');
            $app->get('/home', HomeController::class)->setName('home');
        })->add(new AuthMiddleware());

    })->add(new CsrfMiddleware())->add(new SessionMiddleware());
};
