<?php
declare(strict_types=1);

namespace App\Error\Renderers;

use JsonException;
use Slim\Error\AbstractErrorRenderer;
use Throwable;

class JsonErrorRenderer extends AbstractErrorRenderer
{
    /**
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @return string
     * @throws JsonException
     */
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $payload = [
            'status' => 'error',
            'message' => $this->getErrorTitle($exception)
        ];

        if ($displayErrorDetails) {
            $payload['exception'] = [];
            do {
                $payload['exception'][] = $this->formatExceptionFragment($exception);
            }
            while ($exception = $exception->getPrevious());
        }

        return json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param Throwable $exception
     * @return array
     */
    private function formatExceptionFragment(Throwable $exception): array
    {
        return [
            'type' => get_class($exception),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
    }
}
