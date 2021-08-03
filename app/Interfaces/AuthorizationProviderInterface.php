<?php
declare(strict_types=1);

namespace App\Interfaces;

interface AuthorizationProviderInterface
{
    /**
     * @param string $username
     * @return object|null
     */
    public function getUser(string $username): ?object;
}
