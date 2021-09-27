<?php

namespace App\Classes;

use App\Core;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\ProcessorInterface;
use Monolog\Processor\TagProcessor;
use Psr\Log\LoggerInterface;
use Monolog\Logger as Monolog;
use ReflectionClass;
use ReflectionException;

class Logger
{
    /**
     * Create the logger instance from the LOG_STACK environment variable.
     *
     * @param string $name
     * @return LoggerInterface
     */
    public static function createLogger(string $name): LoggerInterface
    {
        return new Monolog($name, self::createHandlers(), self::createProcessors());
    }

    /**
     * @return HandlerInterface[]
     */
    private static function createHandlers(): array
    {
        $result = [];

        foreach (Core::config('log.stacks')[Core::config('log.stack')] as $handlerName) {
            try {
                $handler = Core::config('log.handlers')[$handlerName];
                $class = new ReflectionClass($handler->handler);
                $result[$handlerName] = $class->newInstanceArgs($handler->params);
            }
            catch (ReflectionException) {
                /* Ignored */
            }
        }

        return $result;
    }

    /**
     * @return ProcessorInterface[]
     */
    private static function createProcessors(): array
    {
        return [
            new MemoryPeakUsageProcessor(),
            new ProcessIdProcessor(),
            new TagProcessor(),
        ];
    }
}
