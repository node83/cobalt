<?php
declare(strict_types=1);

namespace App;

use App\Classes\Database;
use App\Extensions\CoreExtension;
use App\Extensions\CsrfExtension;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\App;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Routing\RouteCollector;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use function Sentry\init;

class Core
{
    private static App $app;
    private static string $root;

    public static function create(string $root): App
    {
        self::$root = $root;

        Dotenv::create(RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(EnvConstAdapter::class)
            ->immutable()->make(), $root)->load();

        if ($dsn = self::env('SENTRY_DSN')) {
            init(['dsn' => $dsn]);
        }

        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'config' => static function() use ($root) {
                $items = [];
                foreach (glob($root . '/config/*php') as $file) {
                    /** @noinspection PhpIncludeInspection */
                    $items[basename($file, '.php')] = require $file;
                }
                return $items;
            },

            Database::class => static function () {
                return new Database(self::config('db.dsn'), self::config('db.user'), self::config('db.password'));
            },

            LoggerInterface::class => static function () {
                return new Logger($_ENV['APP_NAME']);
            },

            Twig::class => static function () {
                $instance = Twig::create(Core::path('templates'), [
                    'cache' => false,
                    'debug' => true,
                    'strict_variables' => true,
                ]);
                $instance->getEnvironment()->addExtension(new CoreExtension());
                $instance->getEnvironment()->addExtension(new CsrfExtension());

                return $instance;
            },

            Filesystem::class => function () {
                return new Filesystem(new LocalFilesystemAdapter(Core::path('storage')));
            }
        ]);

        $container = $builder->build();
        self::$app = $app = Bridge::create($container);

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

        $errorMiddleware = $app->addErrorMiddleware(self::config('app.debug') ?? false, true, true,
            self::get(LoggerInterface::class));
        $errorHandler = $errorMiddleware->getDefaultErrorHandler();
        $errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);

        foreach (glob($root . '/routes/*php') as $file) {
            /** @noinspection PhpIncludeInspection */
            (require $file)($app);
        }
        $container->set(RouteCollector::class, $app->getRouteCollector());

        return $app;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function config(string $key, mixed $default = null): mixed
    {
        return self::dot(self::get('config'), $key, $default);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $default;
        if (is_string($value)) {
            if (!strcasecmp($value, 'true')) {
                return true;
            }
            if (!strcasecmp($value, 'false')) {
                return false;
            }
            if (str_starts_with($value, 'base64:')) {
                return base64_decode(substr($value, 7));
            }
        }
        return $value;
    }

    /**
     * @param array $items
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function dot(array $items, string $key, mixed $default): mixed
    {
        foreach (explode('.', $key) as $part) {
            if (is_array($items)) {
                if (!array_key_exists($part, $items)) {
                    return $default;
                }
                $items = &$items[$part];
            }
        }
        return $items;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public static function get(string $id): mixed
    {
        $container = self::$app->getContainer();

        if (!$container) {
            throw new RuntimeException('Container not defined');
        }

        if (!$container->has($id)) {
            throw new RuntimeException($id . ' has not been defined in the container');
        }

        return $container->get($id);
    }

    /**
     * @param string|null $file
     * @return string
     */
    public static function path(?string $file = null): string
    {
        return '/' . trim(self::$root, '/') . (is_string($file) ? ('/' . trim($file, '/')) : '');
    }
}
