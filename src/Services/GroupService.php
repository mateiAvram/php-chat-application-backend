<?php

namespace App\Services;

use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Group;

use App\Repositories\UserSqlRepository;
use App\Repositories\GroupSqlRepository;
use App\Repositories\MembershipSqlRepository;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class GroupService
{
    public function createGroup(array $cookies, array $data, Request $request): Group
    {
        // Declare repositories used
        $userRepository = new UserSqlRepository();
        $groupRepository = new GroupSqlRepository();
        $membershipRepository = new MembershipSqlRepository();

        // Extract data
        $ownerId  = $cookies['userId'];
        $groupName = $data['groupName'];
        $groupName = trim($groupName);

        // User validation
        $res = $userRepository->getUserById($ownerId);
        if (empty($res)) {
            throw new HttpUnauthorizedException($request, 'User does not exist.');
        }

        // Group validation
        $res = $groupRepository->getGroupByName($groupName);
        if (!empty($res)) {
            throw new HttpBadRequestException($request, 'Group name already taken.');
        }

        // Creating group
        $res = $groupRepository->insertGroup($groupName);
        $groupId = (int)$res[0]['id'];

        // Adding owner to the group
        $membershipRepository->addMember($ownerId, $groupId, 'admin');

        // Returning the newly created group
        return new Group(id: $groupId, name: $groupName);
    }
}
