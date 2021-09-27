<?php
declare(strict_types=1);

namespace App;

use App\Classes\Database;
use App\Classes\Logger;
use App\Classes\Storage;
use App\Classes\View;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Psr\Log\LoggerInterface;
use Respect\Validation\Factory;
use RuntimeException;
use Slim\App;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Routing\RouteCollector;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
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
            /**
             * Config settings
             */
            'config' => static function() use ($root) {
                $items = [];
                foreach (glob($root . '/config/*php') as $file) {
                    $items[basename($file, '.php')] = require $file;
                }
                return $items;
            },

            /**
             * Lightweight PDO wrapper
             */
            Database::class => static function () {
                return new Database(Core::config('db.dsn'), Core::config('db.user'), Core::config('db.password'));
            },

            /**
             * Lightweight Filesystem wrapper
             */
            Storage::class => static function () {
                return new Storage();
            },

            /**
             * Lightweight Logger wrapper
             */
            LoggerInterface::class => static function () {
                return Logger::createLogger(Core::config('app.name'));
            },

            /**
             * Symfony mailer
             */
            MailerInterface::class => static function () {
                return new Mailer(Transport::fromDsn(Core::config('mail.dsn')));
            },

            /**
             * Lightweight Twig wrapper
             */
            View::class => static function () {
                return new View(self::path('templates'));
            }
        ]);

        $builder->addDefinitions(require Core::path('bootstrap/dependencies.php'));

        $container = $builder->build();
        self::$app = $app = Bridge::create($container);

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();

        $errorMiddleware = $app->addErrorMiddleware(self::config('app.debug') ?? false, true, true,
            self::get(LoggerInterface::class));
        $errorHandler = $errorMiddleware->getDefaultErrorHandler();
        $errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);

        (require Core::path('bootstrap/routes.php'))($app);
        $container->set(RouteCollector::class, $app->getRouteCollector());

        Factory::setDefaultInstance(
            (new Factory())
                ->withRuleNamespace('App\\Validation\\Rules')
                ->withExceptionNamespace('App\\Validation\\Exceptions')
        );

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
        if (($id === 'app') || ($id === App::class)) {
            return self::$app;
        }

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
