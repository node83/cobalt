<?php
declare(strict_types=1);

namespace App\Controllers;

use DI\Annotation\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Twig\Error\Error;

abstract class AbstractController
{
    /** @Inject */
    protected Twig $twig;

    /**
     * @param RequestInterface $request
     * @param string $route
     * @param array $data
     * @param array $params
     * @return ResponseInterface
     */
    protected function redirect(RequestInterface $request, string $route, array $data = [],
                                array $params = []): ResponseInterface
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse(StatusCodeInterface::STATUS_FOUND);

        return $response->withHeader('Location', $routeParser->urlFor($route, $data, $params));
    }

    /**
     * @param ResponseInterface $response
     * @param string $template
     * @param array $context
     * @return ResponseInterface
     */
    protected function render(ResponseInterface $response, string $template, array $context = []): ResponseInterface
    {
        // Register @twig (function|filter) [name] extensions on the fly
        $class = new ReflectionClass($this);
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $comment = $method->getDocComment();
            if (is_string($comment) && preg_match('`^\s*\*\s+@twig\s+(filter|function)(\s+([a-z_][a-z0-9_]*))?$`im',
                    $comment, $matches)) {
                $shortName = $method->getShortName();
                $name = count($matches) === 4 ? $matches[3] : $shortName;
                $what = ucfirst(strtolower($matches[1]));
                $twigClass = 'Twig' . $what;
                $addMethod = 'add' . $what;
                $this->twig->getEnvironment()->$addMethod(new $twigClass($name, [$this, $shortName]));
            }
        }

        // Add current user
        if ((session_status() === PHP_SESSION_ACTIVE) && array_key_exists('user', $_SESSION) &&
            !array_key_exists('current_user', $context)) {
            $context['current_user'] = $_SESSION['user'];
        }

        try {
            return $this->twig->render($response, $template, $context);
        }
        catch (Error $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
