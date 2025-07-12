<?php

declare(strict_types=1);

namespace App\Repositories;

class GroupSqlRepository extends SqlRepository
{

    public function getGroupById(int $id)
    {
        $sql = 'SELECT * FROM groups WHERE (id) = (?)';
        $params = [$id];
        return $this->executeQuery($sql, $params);
    }

    public function getGroupByName(string $groupName)
    {
        $sql = 'SELECT * FROM groups WHERE (name) = (?)';
        $params = [$groupName];
        return $this->executeQuery($sql, $params);
    }

    public function insertGroup(string $groupName)
    {
        $sql = 'INSERT INTO groups (name) VALUES (?) RETURNING id';
        $params = [$groupName];
        return $this->executeQuery($sql, $params);
    }
}
