<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;

class CsrfMiddleware implements MiddlewareInterface
{
    protected string $fieldName;
    protected string $sessionVar;

    /**
     * @param string $fieldName
     * @param string $sessionVar
     */
    public function __construct(string $fieldName = 'csrf_token', string $sessionVar = 'csrf_token')
    {
        $this->fieldName = $fieldName;
        $this->sessionVar = $sessionVar;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws HttpBadRequestException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $params = $request->getParsedBody();
            $token = is_array($params) && array_key_exists($this->fieldName, $params) ? $params[$this->fieldName] : '';

            if ($token === '') {
                throw new HttpBadRequestException($request, 'Missing CSRF token');
            }
            if ($token !== $_SESSION[$this->sessionVar]) {
                throw new HttpBadRequestException($request, 'CSRF Token Mismatch');
            }
        }
        return $handler->handle($request);
    }

    /**
     * @return string
     */
    protected function getToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        $token = $_SESSION[$this->sessionVar] ?? null;
        if (is_null($token)) {
            $_SESSION[$this->sessionVar] = $token = uniqid('csrf.', true);
        }

        return $token;
    }
}
