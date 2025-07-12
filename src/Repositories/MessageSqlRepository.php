<?php

declare(strict_types=1);

namespace App\Repositories;

class MessageSqlRepository extends SqlRepository
{
    public function getMessageById(int $id)
    {
        $sql = "SELECT * FROM messages WHERE (id) = (?)";
        $params = [$id];
        return $this->executeQuery($sql, $params);
    }

    public function getMessages(int $groupId, ?int $limit = 10)
    {
        $sql = "SELECT m.id, m.content, m.timestamp, COALESCE(u.id, 1) AS senderId, COALESCE(u.name, 'Deleted User') AS senderName FROM messages AS m LEFT JOIN users as u ON m.senderId = u.id WHERE (m.groupId) = (?) ORDER BY (m.timestamp) DESC LIMIT (?)";
        $params = [$groupId, $limit];
        return $this->executeQuery($sql, $params);
    }

    public function getOldMessages(int $groupId, int $lastId, ?int $limit = 20)
    {
        $sql = "SELECT m.id, m.content, m.timestamp, COALESCE(u.id, 1) AS senderId, COALESCE(u.name, 'Deleted User') AS senderName FROM messages AS m LEFT JOIN users as u ON m.senderId = u.id WHERE (m.groupId) = (?) AND (m.id) < (?) ORDER BY (m.timestamp) DESC LIMIT (?)";
        $params = [$groupId, $lastId, $limit];
        return $this->executeQuery($sql, $params);
    }

    public function getNewMessages(int $groupId, string $since)
    {
        $sql = "SELECT m.id, m.content, m.timestamp, COALESCE(u.id, 1) AS senderId, COALESCE(u.name, 'Deleted User') AS senderName FROM messages AS m LEFT JOIN users as u ON m.senderId = u.id WHERE (m.groupId) = (?) AND (m.timestamp) > (?) ORDER BY (m.timestamp) DESC";
        $params = [$groupId, $since];
        return $this->executeQuery($sql, $params);
    }

    public function getAllMessages(int $groupId)
    {
        $sql = "SELECT m.id, m.content, m.timestamp, COALESCE(u.id, 1) AS senderId, COALESCE(u.name, 'Deleted User') AS senderName FROM messages AS m LEFT JOIN users as u ON m.senderId = u.id WHERE (m.groupId) = (?) ORDER BY (m.timestamp) ASC";
        $params = [$groupId];
        return $this->executeQuery($sql, $params);
    }

    public function insertMessage(int $senderId, int $groupId, string $content, string $timestamp)
    {
        $sql = 'INSERT INTO messages (content, senderId, groupId, timestamp) VALUES (?, ?, ?, ?)';
        $params = [$content, $senderId, $groupId, $timestamp];
        return $this->executeQuery($sql, $params);
    }
}
