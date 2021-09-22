<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class XhrMiddleware implements MiddlewareInterface
{
    /**
     * Ensure the incoming request was made via AJAX
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isXhr($request)) {
            $responseFactory = new ResponseFactory();

            return $responseFactory->createResponse(404);
        }

        return $handler->handle($request);
    }

    private function isXhr(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';
    }
}
