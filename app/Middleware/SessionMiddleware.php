<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    private array $options;

    /**
     * @param string $name
     */
    public function __construct(string $name = 'sid')
    {
        $this->options = [
            'name' => $name,
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->startSessions();

        return $handler->handle($request);
    }

    private function startSessions()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start($this->options);
        }
    }
}
