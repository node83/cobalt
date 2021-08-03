<?php
declare(strict_types=1);

namespace App\Repositories;

class UserRepository extends AbstractRepository
{
    /**
     * @param string $username
     * @return object|null
     */
    public function getByUsername(string $username): ?object
    {
        $sql = 'SELECT * FROM users WHERE username = :username';

        return $this->objectOrNull($this->db->execute($sql, ['username' => $username])->fetch());
    }
}
