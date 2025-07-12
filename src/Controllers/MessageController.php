<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as V;

use App\Services\MessageService;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class MessageController
{

    public function send(Request $request, Response $response, array $args): Response
    {
        // Extract URL args
        $groupId = $args['id'];
        // Extract Request cookies
        $cookies = $request->getCookieParams();
        // Extract Request body
        $data = $request->getParsedBody();

        // Validate URL args
        $validator = V::intVal()->positive();
        if (!$validator->validate($groupId)) {
            throw new HttpBadRequestException($request, 'Missing or invalid group ID.');
        }
        // Validate cookies
        if (!V::key('userId', $validator)->validate($cookies)) {
            throw new HttpUnauthorizedException($request, 'Missing or invalid userId.');
        }
        // Validate Request body
        $validator = V::key(
            'content',
            V::stringType()->notEmpty()
        );
        if (!$validator->validate($data)) {
            throw new HttpBadRequestException($request, 'Missing or invalid JSON body');
        }

        // Passing request to MessageService
        $messageService = new MessageService();
        $messageService->sendMessage($groupId, $cookies, $data, $request);

        // Building payload on successfuly sent message
        $payload = [
            'success' => true,
            'data' => 'message has been sent.'
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function retrieve(Request $request, Response $response, array $args): Response
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

        // Passing request to MessageService
        $messageService = new MessageService();
        $messages = $messageService->retrieveMessages($groupId, $cookies, $request);

        // Building payload on successfuly sent message
        $payload = [
            'success' => true,
            'data' => $messages
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function retrieveOld(Request $request, Response $response, array $args): Response
    {
        // Extract URL args
        $groupId = $args['id'];
        // Extract URL params
        $params = $request->getQueryParams();
        // Extract Request cookies
        $cookies = $request->getCookieParams();

        // Validate URL args
        $validator = V::intVal()->positive();
        if (!$validator->validate($groupId)) {
            throw new HttpBadRequestException($request, 'Missing or invalid group ID.');
        }
        // Validate query params
        if (!V::key('oldest', $validator)->validate($params)) {
            throw new HttpBadRequestException($request, "Missing or invalid 'oldest' message ID.");
        }
        // Validate cookies
        if (!V::key('userId', $validator)->validate($cookies)) {
            throw new HttpUnauthorizedException($request, 'Missing or invalid userId.');
        }

        // Passing request to MessageService
        $messageService = new MessageService();
        $messages = $messageService->retrieveOldMessages($groupId, $params, $cookies, $request);

        // Building payload on successfuly sent message
        $payload = [
            'success' => true,
            'data' => $messages
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function retrieveNew(Request $request, Response $response, array $args): Response
    {
        // Extract URL args
        $groupId = $args['id'];
        // Extract URL params
        $params = $request->getQueryParams();
        // Extract Request cookies
        $cookies = $request->getCookieParams();

        // Validate URL args
        $validator = V::intVal()->positive();
        if (!$validator->validate($groupId)) {
            throw new HttpBadRequestException($request, 'Missing or invalid group ID.');
        }
        // Validate query params
        $validator = V::intVal()->between(0, time());
        if (!V::key('lastUpdate', $validator)->validate($params)) {
            throw new HttpBadRequestException($request, "Missing or invalid 'lastUpdate' timestamp.");
        }
        // Validate cookies
        $validator = V::intVal()->positive();
        if (!V::key('userId', $validator)->validate($cookies)) {
            throw new HttpUnauthorizedException($request, 'Missing or invalid userId.');
        }

        // Passing request to MessageService
        $messageService = new MessageService();
        $messages = $messageService->retrieveNewMessages($groupId, $params, $cookies, $request);

        // Building payload on successfuly sent message
        $payload = [
            'success' => true,
            'data' => $messages
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function retrieveAll(Request $request, Response $response, array $args): Response
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

        // Passing request to MessageService
        $messageService = new MessageService();
        $messages = $messageService->retrieveConversation($groupId, $cookies, $request);

        // Building payload on successfuly sent message
        $payload = [
            'success' => true,
            'data' => $messages
        ];

        // Returning successful response
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}
