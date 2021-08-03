<?php
declare(strict_types=1);

namespace App\Classes;

use PDO;
use PDOException;
use PDOStatement;

class Database extends PDO
{
    /**
     * @param string $dsn
     * @param string|null $user
     * @param string|null $password
     * @param array $options
     */
    public function __construct(string $dsn, ?string $user = null, ?string $password = null, array $options = [])
    {
        parent::__construct($dsn, $user, $password, array_replace([
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8MB4"',
        ], $options));
    }

    /**
     * @param string $sql
     * @param array $context
     * @return PDOStatement
     * @throws PDOException
     */
    public function execute(string $sql, array $context = []): PDOStatement
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($context);

        return $stmt;
    }
}
