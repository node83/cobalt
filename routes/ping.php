<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Slim\App;

return static function (App $app) {
    $app->get('/ping', function (ResponseInterface $response): ResponseInterface {
        $response->getBody()->write('pong');
        return $response->withHeader('Content-Type', 'text/plain');
    });
};
