<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Classes\View;
use DI\Annotation\Inject;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;
use Twig\Error\Error;

abstract class Controller
{
    /** @Inject */
    protected View $view;

    /**
     * @param ServerRequestInterface $request
     * @param string $route
     * @param array $data
     * @param array $params
     * @return ResponseInterface
     */
    protected function redirect(ServerRequestInterface $request, string $route, array $data = [],
                                array $params = []): ResponseInterface
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse(StatusCodeInterface::STATUS_FOUND);

        return $response->withHeader('Location', $routeParser->urlFor($route, $data, $params));
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $template
     * @param array $context
     * @return ResponseInterface
     * @throws Exception
     */
    protected function render(ServerRequestInterface $request, string $template, array $context = []): ResponseInterface
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
                $this->view->getEnvironment()->$addMethod(new $twigClass($name, [$this, $shortName]));
            }
        }

        // Add current user
        if ((session_status() === PHP_SESSION_ACTIVE) && array_key_exists('user', $_SESSION) &&
            !array_key_exists('current_user', $context)) {
            $context['current_user'] = $_SESSION['user'];
        }

        try {
            $responseFactory = new ResponseFactory();
            $response = $responseFactory->createResponse();

            $markup = $this->view->render($request, $template, $context);
            $response->getBody()->write($markup);

            return $response;
        }
        catch (Error $e) {
            throw new Exception($e->getMessage());
        }
    }
}
