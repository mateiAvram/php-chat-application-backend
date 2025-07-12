<?php

namespace App\Services;

use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\User;

use App\Repositories\UserSqlRepository;
use App\Repositories\GroupSqlRepository;
use App\Repositories\MembershipSqlRepository;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class UserService
{
    public function createUser(array $data, Request $request): User
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();

        // Extract data
        $username = $data['username'];
        $username = trim($username);

        $res = $userRepository->getUserByName($username);
        if (!empty($res)) {
            throw new HttpBadRequestException($request, 'Username already in use.');
        }

        // Creating new user
        $res = $userRepository->insertUser($username);
        $userId = (int)$res[0]['id'];

        // Returning the newly created user
        return new User(id: $userId, name: $username);
    }

    public function joinGroup(int $groupId, array $cookies, Request $request): void
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
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
        if (!empty($res)) {
            throw new HttpBadRequestException($request, 'User is already part of this group.');
        }

        // Adding user to the group
        $membershipRepository->addMember($userId, $groupId, 'member');
    }
}
