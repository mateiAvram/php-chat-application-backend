<?php

declare(strict_types=1);

namespace App\Repositories;

class UserSqlRepository extends SqlRepository
{
    public function getUserById(int $id)
    {
        $sql = 'SELECT * FROM users WHERE (id) = (?)';
        $params = [$id];
        return $this->executeQuery($sql, $params);
    }

    public function getUserByName(string $username)
    {
        $sql = 'SELECT * FROM users WHERE (name) = (?)';
        $params = [$username];
        return $this->executeQuery($sql, $params);
    }

    public function insertUser(string $username)
    {
        $sql = 'INSERT INTO users (name) VALUES (?) RETURNING id';
        $params = [$username];
        return $this->executeQuery($sql, $params);
    }
}
