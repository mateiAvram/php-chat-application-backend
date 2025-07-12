<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class Message implements JsonSerializable
{

    private int $id;
    private string $content;
    private int  $senderId;
    private int $groupId;
    private \DateTimeImmutable $timestamp;

    // Constructor
    public function __construct(string $content, int $senderId, int $groupId, ?int $id = null, ?\DateTimeImmutable $timestamp = null)
    {
        $this->id = $id;
        $this->content = $content;
        $this->senderId = $senderId;
        $this->groupId = $groupId;
        $this->timestamp = $timestamp;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function getSenderId(): int
    {
        return $this->senderId;
    }
    public function getGroupId(): int
    {
        return $this->groupId;
    }
    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    // Setters
    public function setContent($content): void
    {
        $this->content = $content;
    }
    public function setSenderId($senderId): void
    {
        $this->senderId = $senderId;
    }
    public function setGroupId($groupId): void
    {
        $this->groupId = $groupId;
    }

    // JSON serializable
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'content'   => $this->content,
            'senderId'  => $this->senderId,
            'groupId'   => $this->groupId,
            'timestamp' => $this->timestamp->format(\DateTime::ATOM),
        ];
    }
}
