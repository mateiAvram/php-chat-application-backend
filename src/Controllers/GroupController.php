<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as V;

use App\Services\GroupService;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class GroupController
{

    public function create(Request $request, Response $response): Response
    {
        // Extract Request cookies
        $cookies = $request->getCookieParams();
        // Extract Request body
        $data = $request->getParsedBody();

        // Validate Request cookies
        $validator = V::intVal()->positive();
        if (!V::key('userId', $validator)->validate($cookies)) {
            throw new HttpUnauthorizedException($request, 'Missing or invalid userId.');
        }
        // Validate Request body
        $validator = V::key(
            'groupName',
            V::stringType()->alnum()->notEmpty()->length(1, 50)
        );
        if (!$validator->validate($data)) {
            throw new HttpBadRequestException($request, 'Missing or invalid JSON body.');
        }

        // Passing request to GroupService
        $groupService = new GroupService();
        $group = $groupService->createGroup($cookies, $data, $request);

        // Building payload with newly created group
        $payload = [
            'success' => true,
            'data' => $group->jsonSerialize()
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }
}
