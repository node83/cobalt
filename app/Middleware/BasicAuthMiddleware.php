<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Classes\Database;
use Fig\Http\Message\StatusCodeInterface;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class BasicAuthMiddleware implements MiddlewareInterface
{
    protected Database $db;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $credentials = $this->getCredentials($request);
        if (!$credentials) {
            return $this->error('Missing or malformed authorization header')
                ->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        $sql = 'SELECT * FROM `users` WHERE `username` = :user';
        $user = $this->db->execute($sql, ['user' => $credentials->user])->fetch();
        if (!$user) {
            return $this->error('Authorization failed')->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        return $handler->handle($request->withAttribute('user', $user));
    }

    /**
     * @param ServerRequestInterface $request
     * @return object|null
     */
    private function getCredentials(ServerRequestInterface $request): ?object
    {
        $header = $request->getHeader('Authorization');
        if (!$header) {
            return null;
        }

        if (!preg_match('`^Basic\s+(.+)$`i', $header[0], $matches)) {
            return null;
        }

        $credentials = base64_decode($matches[1]);
        if (!$credentials || !preg_match('`^(\S+):(\S+)$`', $credentials, $matches)) {
            return null;
        }

        return (object)['user' => $matches[1], 'password' => $matches[2]];
    }

    /**
     * @param string $message
     * @return ResponseInterface
     * @throws JsonException
     */
    private function error(string $message): ResponseInterface
    {
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        return $response;
    }
}
