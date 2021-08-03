<?php
declare(strict_types=1);

namespace App\Providers;

use App\Classes\Database;
use App\Interfaces\AuthorizationProviderInterface;

class DatabaseAuthorizationProvider implements AuthorizationProviderInterface
{
    protected Database $db;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $username
     * @return object|null
     */
    public function getUser(string $username): ?object
    {
        $sql = 'SELECT * FROM `users` WHERE `username` = :username';
        $user = $this->db->execute($sql, ['username' => $username])->fetch();

        return is_object($user) ? $user : null;
    }
}
