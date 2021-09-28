<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Classes\Database;
use DI\Annotation\Inject;

class Repository
{
    /** @Inject */
    protected Database $db;

    /**
     * @param mixed $value
     * @return object|null
     */
    protected function objectOrNull(mixed $value): ?object
    {
        return is_object($value) ? $value : null;
    }
}
