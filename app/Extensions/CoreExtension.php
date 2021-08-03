<?php
declare(strict_types=1);

namespace App\Extensions;

use App\Core;
use JetBrains\PhpStorm\Pure;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CoreExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            // TODO map php_* to php functions ? risky!
            new TwigFunction('ceil', 'ceil'),
            new TwigFunction('floor', 'floor'),

            new TwigFunction('config', [$this, 'configFunction']),
            new TwigFunction('env', [$this, 'envFunction']),
            new TwigFunction('static', [$this, 'staticFunction']),
        ];
    }

    public function configFunction(string $key, mixed $default = null): mixed
    {
        return Core::config($key, $default);
    }

    public function envFunction(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    #[Pure]
    public function staticFunction(string $file): string
    {
        $path = Core::path() . '/public/static/' . ltrim($file, '/');
        return '/static/' . ltrim($file, '/') . (file_exists($path) ? ('?v=' . filemtime($path)) : '');
    }
}
