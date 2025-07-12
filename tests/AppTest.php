<?php

declare(strict_types=1);

use Slim\Psr7\Factory\ServerRequestFactory;

class AppTest extends BaseTestCase
{
    /**
     * Creates a user and returns its generated ID.
     */
    public function testCreateUserSetsCookie(): int
    {
        $payload = ['username' => 'alice'];
        $response = $this->runApp('POST', '/user/create', $payload);

        $this->assertEquals(201, $response->getStatusCode());

        $cookieHeader = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('userId=', $cookieHeader);

        preg_match('/userId=(\d+);/', $cookieHeader, $matches);
        $this->assertNotEmpty($matches[1], 'userId cookie should exist');
        return (int)$matches[1];
    }

    /**
     * @depends testCreateUserSetsCookie
     */
    public function testCreateGroup(int $userId): int
    {
        $cookie = ['userId' => $userId];
        $response = $this->runApp(
            'POST',
            '/group/create',
            [
                'groupName' => 'Testers'
            ],
            $cookie
        );

        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('success', $body);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('id', $body['data']);
        $this->assertArrayHasKey('name', $body['data']);

        return (int)$body['data']['id'];
    }

    public function testCreateGroupUnauthorized(): void
    {
        $response = $this->runApp('POST', '/group/create', ['groupName' => 'X'], []);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @depends testCreateGroup
     */
    public function testJoinGroup(int $groupId): int
    {

        // Create new user
        $payload = ['username' => 'bob'];
        $response = $this->runApp('POST', '/user/create', $payload);

        $this->assertEquals(201, $response->getStatusCode());

        $cookieHeader = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('userId=', $cookieHeader);

        preg_match('/userId=(\d+);/', $cookieHeader, $matches);
        $this->assertNotEmpty($matches[1], 'userId cookie should exist');
        $userId = (int)$matches[1];

        // Have the user join the existing group
        $cookie = ['userId' => $userId];
        $response = $this->runApp('POST', "/group/{$groupId}/join", [], $cookie);
        $this->assertEquals(200, $response->getStatusCode());

        return $userId;
    }

    /**
     * @depends testCreateGroup
     */
    public function testJoinGroupForbidden(int $groupId): void
    {
        $response = $this->runApp(
            'POST',
            "/group/{$groupId}/join",
            [],
            ['userId' => 999]
        );
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @depends testCreateGroup
     * @depends testCreateUserSetsCookie
     * @depends testJoinGroup
     */
    public function testSendMessage(int $groupId, int $userId1, int $userId2): void
    {
        $cookie = ['userId' => $userId1];
        $resp = $this->runApp(
            'POST',
            "/group/{$groupId}/send",
            ['content' => 'Hello world 1!'],
            $cookie
        );

        $this->assertEquals(200, $resp->getStatusCode());

        $cookie = ['userId' => $userId2];
        $resp = $this->runApp(
            'POST',
            "/group/{$groupId}/send",
            ['content' => 'Hello world 2!'],
            $cookie
        );

        $this->assertEquals(200, $resp->getStatusCode());
    }

    /**
     * @depends testCreateGroup
     * @depends testCreateUserSetsCookie
     */
    public function testSendMessageValidationError(int $groupId, int $userId): void
    {
        $response = $this->runApp(
            'POST',
            "/group/{$groupId}/send",
            [],
            ['userId' => $userId]
        );
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @depends testCreateGroup
     * @depends testCreateUserSetsCookie
     */
    public function testRetrieveAllMessages(int $groupId, int $userId): void
    {
        // Use the correct cookie key:
        $cookie = ['userId' => $userId];

        // 1) Retrieve all messages
        $respAll = $this->runApp(
            'GET',
            "/group/{$groupId}/messages/all",
            [],
            $cookie
        );
        $this->assertEquals(200, $respAll->getStatusCode());

        $bodyAll = json_decode((string)$respAll->getBody(), true);
        // Top‐level
        $this->assertTrue($bodyAll['success']);
        $this->assertIsArray($bodyAll['data']);

        // First msg
        $msg = $bodyAll['data'][0];
        $this->assertArrayHasKey('content',  $msg);
        $this->assertEquals('Hello world 1!', $msg['content']);

        // Second msg
        $msg = $bodyAll['data'][1];
        $this->assertArrayHasKey('content',  $msg);
        $this->assertEquals('Hello world 2!', $msg['content']);
    }

    /**
     * @depends testCreateGroup
     */
    public function testRetrieveMessagesUnauthorized(int $groupId): void
    {
        $resp = $this->runApp('GET', "/group/{$groupId}/messages/all", [], []);
        $this->assertEquals(401, $resp->getStatusCode());
    }

    /**
     * @depends testCreateGroup
     */
    public function testRetrieveMessagesForbidden(int $groupId): void
    {
        $resp = $this->runApp(
            'GET',
            "/group/{$groupId}/messages/all",
            [],
            ['userId' => 999]
        );
        $this->assertEquals(401, $resp->getStatusCode());
    }
}
