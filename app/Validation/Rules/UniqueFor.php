<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Classes\Database;
use App\Core;
use Exception;
use Respect\Validation\Rules\AbstractRule;

class UniqueFor extends AbstractRule
{
    private Database $db;
    private string $where;
    private int $id;

    // Pass in <table>:<field>[:<primary-key>]
    public function __construct(string $where, int $id = 0)
    {
        $this->db = Core::get(Database::class);
        $this->where = $where;
        $this->id = $id;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($input): bool
    {
        if (!is_string($input)) {
            throw new Exception('Invalid data');
        }

        $parts = explode(':', $this->where);
        $primary_key = count($parts) === 3 ? sprintf('`%s`', $parts[2]) : '`id`';
        $sql = sprintf('SELECT COUNT(%s) FROM `%s` WHERE `%s` = :value', $primary_key, $parts[0], $parts[1]);
        $args = ['value' => $input];

        if ($this->id) {
            $sql .= sprintf(' AND %s != :id', $primary_key);
            $args['id'] = $this->id;
        }

        return (int)$this->db->execute($sql, $args)->fetchColumn() === 0;
    }
}
