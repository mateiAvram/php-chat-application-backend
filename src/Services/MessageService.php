<?php

namespace App\Services;

use Psr\Http\Message\ServerRequestInterface as Request;

use App\Repositories\UserSqlRepository;
use App\Repositories\GroupSqlRepository;
use App\Repositories\MessageSqlRepository;
use App\Repositories\MembershipSqlRepository;

use DateTimeZone;
use DateTimeImmutable;
use DateTimeInterface;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;

class MessageService
{
    public function sendMessage(int $groupId, array $cookies, array $data, Request $request): void
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
        $messageRepository = new MessageSqlRepository();
        $membershipRepository = new MembershipSqlRepository();

        // Extract data
        $senderId  = $cookies['userId'];
        $content = $data['content'];
        $content = trim($content);

        $res = $userRepository->getUserById($senderId);
        if (empty($res)) {
            throw new HttpUnauthorizedException($request, 'User does not exist.');
        }

        // Group validation
        $res = $groupRepository->getGroupById($groupId);
        if (empty($res)) {
            throw new HttpBadRequestException($request, 'Group does not exist.');
        }

        // Membership validation
        $res = $membershipRepository->isMember($senderId, $groupId);
        if (empty($res)) {
            throw new HttpForbiddenException($request, 'User not part of the group.');
        }

        // Creating message
        $timestamp = new \DateTimeImmutable('now', new \DateTimeZone('UTC'))->format(DateTimeImmutable::ATOM);
        $messageRepository->insertMessage($senderId, $groupId, $content, $timestamp);
    }

    public function retrieveMessages(int $groupId, array $cookies, Request $request)
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
        $messageRepository = new MessageSqlRepository();
        $membershipRepository = new MembershipSqlRepository();

        // Extract data
        $userId = $cookies['userId'];

        // User validation
        $res = $userRepository->getUserById($userId);
        if (empty($res)) {
            throw new HttpUnauthorizedException($request, 'User does not exist.');
        }

        // Group validation
        $res = $groupRepository->getGroupById($groupId);
        if (empty($res)) {
            throw new HttpBadRequestException($request, 'Group does not exist.');
        }

        // Membership validation
        $res = $membershipRepository->isMember($userId, $groupId);
        if (empty($res)) {
            throw new HttpForbiddenException($request, 'User not part of the group.');
        }

        // Retrieving messages
        $res = $messageRepository->getMessages($groupId);
        if (empty($res)) {
            return [];
        }

        return array_map(function (array $row): array {
            return [
                'id'        => (int)$row['id'],
                'content'   => $row['content'],
                'timestamp' => $row['timestamp'],
                'sender'    => [
                    'id'   => (int)$row['senderId'],
                    'name' => $row['senderName'],
                ],
            ];
        }, $res);
    }

    public function retrieveOldMessages(int $groupId, array $params, array $cookies, Request $request)
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
        $messageRepository = new MessageSqlRepository();
        $membershipRepository = new MembershipSqlRepository();

        // Extract data
        $userId = $cookies['userId'];
        $oldestId = $params['oldest'];

        // User validation
        $res = $userRepository->getUserById($userId);
        if (empty($res)) {
            throw new HttpUnauthorizedException($request, 'User does not exist.');
        }

        // Group validation
        $res = $groupRepository->getGroupById($groupId);
        if (empty($res)) {
            throw new HttpBadRequestException($request, 'Group does not exist.');
        }

        // Membership validation
        $res = $membershipRepository->isMember($userId, $groupId);
        if (empty($res)) {
            throw new HttpForbiddenException($request, 'User not part of the group.');
        }

        // Last message validation
        $res = $messageRepository->getMessageById($oldestId);
        if (empty($res)) {
            throw new HttpBadRequestException($request, 'Oldest messageId provided does not exist.');
        }

        // Retrieving old messages
        $res = $messageRepository->getOldMessages($groupId, $oldestId);
        if (empty($res)) {
            return [];
        }

        return array_map(function (array $row): array {
            return [
                'id'        => (int)$row['id'],
                'content'   => $row['content'],
                'timestamp' => $row['timestamp'],
                'sender'    => [
                    'id'   => (int)$row['senderId'],
                    'name' => $row['senderName'],
                ],
            ];
        }, $res);
    }

    public function retrieveNewMessages(int $groupId, array $params, array $cookies, Request $request)
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
        $messageRepository = new MessageSqlRepository();
        $membershipRepository = new MembershipSqlRepository();

        // Extract data
        $userId = $cookies['userId'];
        $since = $params['since'];
        $since = (new \DateTimeImmutable("@{$since}"))->setTimezone(new \DateTimezone('UTC'))->format(\DateTimeInterface::ATOM);

        // User validation
        $res = $userRepository->getUserById($userId);
        if (empty($res)) {
            throw new HttpUnauthorizedException($request, 'User does not exist.');
        }

        // Group validation
        $res = $groupRepository->getGroupById($groupId);
        if (empty($res)) {
            throw new HttpBadRequestException($request, 'Group does not exist.');
        }

        // Membership validation
        $res = $membershipRepository->isMember($userId, $groupId);
        if (empty($res)) {
            throw new HttpForbiddenException($request, 'User not part of the group.');
        }

        // Retrieving old messages
        $res = $messageRepository->getNewMessages($groupId, $since);
        if (empty($res)) {
            return [];
        }

        return array_map(function (array $row): array {
            return [
                'id'        => (int)$row['id'],
                'content'   => $row['content'],
                'timestamp' => $row['timestamp'],
                'sender'    => [
                    'id'   => (int)$row['senderId'],
                    'name' => $row['senderName'],
                ],
            ];
        }, $res);
    }

    public function retrieveConversation(int $groupId, array $cookies, Request $request)
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
        $messageRepository = new MessageSqlRepository();
        $membershipRepository = new MembershipSqlRepository();

        // Extract data
        $userId = $cookies['userId'];

        // User validation
        $res = $userRepository->getUserById($userId);
        if (empty($res)) {
            throw new HttpUnauthorizedException($request, 'User does not exist.');
        }

        // Group validation
        $res = $groupRepository->getGroupById($groupId);
        if (empty($res)) {
            throw new HttpBadRequestException($request, 'Group does not exist.');
        }

        // Membership validation
        $res = $membershipRepository->isMember($userId, $groupId);
        if (empty($res)) {
            throw new HttpForbiddenException($request, 'User not part of the group.');
        }

        // Retrieving messages
        $res = $messageRepository->getAllMessages($groupId);
        if (empty($res)) {
            return [];
        }

        return array_map(function (array $row): array {
            return [
                'id'        => (int)$row['id'],
                'content'   => $row['content'],
                'timestamp' => $row['timestamp'],
                'sender'    => [
                    'id'   => (int)$row['senderId'],
                    'name' => $row['senderName'],
                ],
            ];
        }, $res);
    }
}
