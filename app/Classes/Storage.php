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

    /**
     * Storage Constructor. Builds and attaches each storage adapter as defined in config/storage.php
     */
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

    /**
     * @param string $key
     * @return FilesystemAdapter
     */
    public function __get(string $key): FilesystemAdapter
    {
        return $this->adapters[$key];
    }

    /**
     * @param string $key
     * @param FilesystemAdapter $value
     */
    public function __set(string $key, FilesystemAdapter $value): void
    {
        $this->adapters[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->adapters);
    }
}
