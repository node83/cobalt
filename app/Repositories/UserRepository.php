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
        $sql = 'SELECT * FROM `users` WHERE `username` = :username';

        return $this->objectOrNull($this->db->execute($sql, ['username' => $username])->fetch());
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param bool $staff
     * @param bool $superuser
     * @return object|null
     */
    public function addUser(string $username, string $password, string $email, bool $staff = false,
                            bool $superuser = false): ?object
    {
        $sql = <<<SQL
            INSERT INTO `users` (`username`, `password`, `email`, `staff`, `superuser`)
            VALUES (:username, :password, :email, :staff, :superuser)
SQL;
        if (!$this->db->execute($sql, [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'staff' => (int)$staff,
            'superuser' => (int)$superuser,
        ])->rowCount()) {
            return null;
        }

        return $this->getByUsername($username);
    }
}
