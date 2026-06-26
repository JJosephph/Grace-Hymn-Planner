<?php

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    protected function prepare(string $sql): PDOStatement
    {
        return $this->db->prepare(Database::prefixSql($sql));
    }

    protected function query(string $sql): PDOStatement
    {
        return $this->db->query(Database::prefixSql($sql));
    }

    protected function table(string $name): string
    {
        return Database::table($name);
    }
}
