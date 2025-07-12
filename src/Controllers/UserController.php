<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as V;

use App\Services\UserService;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class UserController
{

    public function create(Request $request, Response $response): Response
    {
        // Extract Request body
        $data = $request->getParsedBody();

        // Validate Request body
        $validator = V::key('username', V::alnum()->noWhitespace()->length(3, 20));
        if (!$validator->validate($data)) {
            throw new HttpBadRequestException($request, 'Missing or invalid JSON body.');
        }

        // Passing request to UserService
        $userService = new UserService();
        $user = $userService->createUser($data, $request);

        // Building payload with newly created user
        $payload = [
            'success' => true,
            'data' => $user->jsonSerialize()
        ];

        // Creating userId cookie for authentication
        $cookie  = sprintf(
            'userId=%s; Path=/; HttpOnly; Secure; SameSite=Strict',
            rawurlencode($user->getId())
        );

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json')->withHeader('Set-Cookie', $cookie);
    }

    public function join(Request $request, Response $response, array $args): Response
    {
        // Extract URL args
        $groupId = $args['id'];
        // Extract Request cookies
        $cookies = $request->getCookieParams();

        // Validate URL args
        $validator = V::intVal()->positive();
        if (!$validator->validate($groupId)) {
            throw new HttpBadRequestException($request, 'Missing or invalid group ID.');
        }
        // Validate cookies
        if (!V::key('userId', $validator)->validate($cookies)) {
            throw new HttpUnauthorizedException($request, 'Missing or invalid userId.');
        }

        // Pass request to UserService
        $userService = new UserService();
        $userService->joinGroup($groupId, $cookies, $request);

        $payload = [
            'success' => true,
            'data' => 'user has been added to the group.'
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}
