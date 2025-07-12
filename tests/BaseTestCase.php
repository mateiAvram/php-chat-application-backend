<?php
// tests/BaseTestCase.php

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Psr7\Factory\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;

class BaseTestCase extends TestCase
{
    protected $app;
    protected static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        // Open the one and only database file
        self::$pdo = new PDO('sqlite:' . __DIR__ . '/../database.db');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Clear it exactly once before any tests run
        self::$pdo->exec('DELETE FROM membership;');
        self::$pdo->exec('DELETE FROM messages;');
        self::$pdo->exec('DELETE FROM groups;');
        self::$pdo->exec('DELETE FROM users;');
        self::$pdo->exec('DELETE FROM sqlite_sequence;');
    }

    protected function setUp(): void
    {
        // Reuse the same PDO for every test (no per-test truncation)
        $container = new Container();
        $container->set('db', fn() => self::$pdo);

        AppFactory::setContainer($container);
        $this->app = AppFactory::create();

        // Instantiate Slim and wire up middleware + routes
        $this->app = AppFactory::create();
        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();
        $errorMw = $this->app->addErrorMiddleware(false, false, false);
        $errorMw->getDefaultErrorHandler()->forceContentType('application/json');

        // Routes
        $this->app->get('/hello_world', [\App\Controllers\HelloController::class, 'helloWorld']);
        // Users
        $this->app->post('/user/create', [\App\Controllers\UserController::class, 'create']);
        $this->app->post('/group/{id}/join', [\App\Controllers\UserController::class, 'join']);
        // Groups
        $this->app->post('/group/create', [\App\Controllers\GroupController::class, 'create']);
        // Messages
        $this->app->post('/group/{id}/send', [\App\Controllers\MessageController::class, 'send']);
        $this->app->get(
            '/group/{id}/messages/new',
            [\App\Controllers\MessageController::class, 'retrieveNew']
        );
        $this->app->get(
            '/group/{id}/messages/all',
            [\App\Controllers\MessageController::class, 'retrieveAll']
        );
    }

    protected function runApp(
        string $method,
        string $path,
        array  $payload = [],
        array  $cookies = []
    ): ResponseInterface {
        // 1) Sync PHP superglobal
        $_COOKIE = $cookies;

        // 2) Build PSR-7 request
        $reqFactory = new ServerRequestFactory();
        $request = $reqFactory
            ->createServerRequest($method, $path)
            ->withCookieParams($cookies);

        // 3) JSON body
        if (! empty($payload)) {
            $streamFactory = new StreamFactory();
            $json = json_encode($payload);
            $request = $request
                ->withBody($streamFactory->createStream($json))
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Accept', 'application/json');
        }

        return $this->app->handle($request);
    }
}
