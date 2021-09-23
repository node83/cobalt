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
