<?php
declare(strict_types=1);

namespace App\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;

class AuthMiddleware implements MiddlewareInterface
{
    protected string $loginRoute;

    /**
     * @param string $loginRoute
     */
    public function __construct(string $loginRoute = 'login')
    {
        $this->loginRoute = $loginRoute;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->user();
        if (!$user) {
            return $this->redirect($request, $this->loginRoute);
        }

        return $handler->handle($request->withAttribute('user', $user));
    }

    /**
     * @param RequestInterface $request
     * @param string $route
     * @param array $data
     * @param array $params
     * @return ResponseInterface
     */
    protected function redirect(RequestInterface $request, string $route, array $data = [], array $params = []): ResponseInterface
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse(StatusCodeInterface::STATUS_FOUND);

        return $response->withHeader('Location', $routeParser->urlFor($route, $data, $params));
    }

    /**
     * @return object|null
     */
    protected function user(): ?object
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return $_SESSION['user'] ?? null;
        }

        return null;
    }
}
