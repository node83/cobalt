#!/usr/bin/env php
<?php
declare(strict_types=1);

use App\Core;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

require __DIR__ . '/vendor/autoload.php';

/**
 * @param string $path
 * @param string $namespace
 * @return array
 */
function getCommands(string $path, string $namespace): array
{
    $items = [];

    foreach (glob($path . '/' . '*.php') as $file) {
        $base = basename(substr($file, strlen($path) + 1), '.php');
        $class = '\\' . $namespace . '\\' . $base;

        $name = preg_replace_callback('`([A-Z]+)`', static function ($matches) {
            return ':' . strtolower($matches[0]);
        }, lcfirst($base));

        $items[$name] = static function () use ($class) {
            return new $class;
        };
    }

    return $items;
}

Core::create(__DIR__);

$app = new Application();
$app->setName('Cobalt CLI Tool');
$app->setVersion('1.0.0');
$app->setCommandLoader(new FactoryCommandLoader(getCommands(__DIR__ . '/app/Commands', 'App\Commands')));
$app->run();
