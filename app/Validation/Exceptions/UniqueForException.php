<?php
declare(strict_types=1);

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class UniqueForException extends ValidationException
{
    /**
     * {@inheritDoc}
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be unique',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be unique',
        ],
    ];

}
