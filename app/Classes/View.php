<?php

namespace App\Classes;

use App\Core;
use Exception;
use Psr\Http\Message\{ServerRequestInterface, UriInterface};
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class View
{
    private Environment $twig;
    private RouteParserInterface $routeParser;
    private UriInterface $uri;
    private string $basePath;

    /**
     * View Constructor.
     *
     * @param string $templatePath
     */
    public function __construct(string $templatePath)
    {
        $this->twig = new Environment(new FilesystemLoader($templatePath), [
            'debug' => true,
            'charset' => 'UTF-8',
            'strict_variables' => true,
            'cache' => false,
            'auto_reload' => null,
        ]);

        $this->twig->addExtension(new DebugExtension());

        $this->twig->addFunction(new TwigFunction('url_for', [$this, 'urlFor']));
        $this->twig->addFunction(new TwigFunction('full_url_for', [$this, 'fullUrlFor']));
        $this->twig->addFunction(new TwigFunction('is_current_url', [$this, 'isCurrentUrl']));
        $this->twig->addFunction(new TwigFunction('current_url', [$this, 'currentUrl']));
        $this->twig->addFunction(new TwigFunction('get_uri', [$this, 'getUri']));
        $this->twig->addFunction(new TwigFunction('base_path', [$this, 'basePath']));

        $this->twig->addFunction(new TwigFunction('query_for', [$this, 'queryFor']));
        $this->twig->addFunction(new TwigFunction('csrf_token', [$this, 'csrfToken']));
        $this->twig->addFunction(new TwigFunction('page_list', [$this, 'pageList']));

        $this->twig->addFunction(new TwigFunction('config', [$this, 'coreConfig']));
        $this->twig->addFunction(new TwigFunction('env', [$this, 'coreEnv']));
        $this->twig->addFunction(new TwigFunction('static', [$this, 'staticFunction']));
    }

    /**
     * Returns the Twig environment for adding extensions, filters, functions etc.
     *
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->twig;
    }

    /**
     * Renders the template with the provided context.
     *
     * @param ServerRequestInterface $request
     * @param string $template
     * @param array|object $context
     * @return string
     * @throws Error
     */
    public function render(ServerRequestInterface $request, string $template, array|object $context = []): string
    {
        $this->routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $this->basePath = Core::get('app')->getBasePath();
        $this->uri = $request->getUri();

        return $this->twig->render($template, (array)$context);
    }

    /**
     * Extension Functions
     * These should NOT be called directly as they rely on setup from the request.
     */

    /**
     * @param string $routeName
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string $routeName
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->fullUrlFor($this->uri, $routeName, $data, $queryParams);
    }

    /**
     * @param string $routeName
     * @param array $data
     * @return bool
     */
    public function isCurrentUrl(string $routeName, array $data = []): bool
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $result = $this->routeParser->urlFor($routeName, $data);

        return $result === $currentUrl;
    }

    /**
     * @param bool $withQueryString
     * @return string
     */
    public function currentUrl(bool $withQueryString = false): string
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $query = $this->uri->getQuery();

        if ($withQueryString && !empty($query)) {
            $currentUrl .= '?'.$query;
        }

        return $currentUrl;
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param array $params
     * @return string
     */
    public function queryFor(array $params = []): string
    {
        $result = [];
        foreach (explode('&', $_SERVER['QUERY_STRING'] ?? '') as $item) {
            [$key, $value] = explode('=', $item);
            $result[$key] = $value;
        }

        foreach ($params as $key => $value) {
            $result[$key] = $value;
        }

        $result = array_filter($result, function ($value) {
            return ($value ?? '') !== '';
        });

        $final = [];
        foreach ($result as $key => $value) {
            $final[] = $key . '=' . $value;
        }

        return count($result) ? ('?' . implode('&', $final)) : '';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        $token = $_SESSION['csrf_token'] ?? null;
        if (is_null($token)) {
            $_SESSION['csrf_token'] = $token = bin2hex(random_bytes(32));
        }

        return $token;
    }

    /**
     * @param int $page
     * @param int $pages
     * @return int[]
     */
    public function pageList(int $page, int $pages): array
    {
        $list = array_unique(array_filter([1, 2, $page - 2, $page - 1, $page, $page + 1, $page + 2, $pages - 1, $pages],
            function ($page) use ($pages) {
                return $page > 0 && $page <= $pages;
            }
        ));

        sort($list);

        return $list;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function coreConfig(string $key, mixed $default = null): mixed
    {
        return Core::config($key, $default);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function coreEnv(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    /**
     * @param string $file
     * @return string
     */
    public function staticFunction(string $file): string
    {
        $path = Core::path('public/static/' . ltrim($file, '/'));
        if (file_exists($path)) {
            $path = realpath($path);
            if (str_starts_with($path, Core::path('public'))) {
                return substr($path, strlen(Core::path('public')), strlen($path)) . '?v=' . filemtime($path);
            }
        }

        return '/static/' . ltrim($file, '/');
    }
}
