<?php

declare(strict_types=1);

namespace App\Repositories;

class MembershipSqlRepository extends SqlRepository
{
    public function addMember(int $userId, int $groupId, ?string $role = 'user')
    {
        $sql = 'INSERT INTO membership (userId, groupId, role) VALUES (?, ?, ?)';
        $params = [$userId, $groupId, $role];
        return $this->executeQuery($sql, $params);
    }

    public function isMember(int $userId, int $groupId)
    {
        $sql = 'SELECT * FROM membership WHERE (userId) = (?) AND (groupId) = (?)';
        $params = [$userId, $groupId];
        return $this->executeQuery($sql, $params);
    }
}
