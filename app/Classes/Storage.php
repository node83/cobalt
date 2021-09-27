<?php

namespace App\Classes;

use App\Core;
use League\Flysystem\FilesystemAdapter;
use ReflectionClass;

/**
 * @property FilesystemAdapter $local
 * @property FilesystemAdapter $public
 */
class Storage
{
    private array $adapters;

    public function __construct()
    {
        foreach (Core::config('storage') as $key => $value) {
            try {
                $class = new ReflectionClass($value['driver']);
                $this->adapters[$key] = $class->newInstanceArgs($value['params']);
            }
            catch (\ReflectionException $e) {
                /* Storage driver not available ... */
            }
        }
    }

    public function __get(string $key): FilesystemAdapter
    {
        return $this->adapters[$key];
    }

    public function __set(string $key, FilesystemAdapter $value): void
    {
        $this->adapters[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->adapters);
    }
}
