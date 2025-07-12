<?php
// src/app.php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;
use Slim\App;

return function (?ContainerInterface $container = null): App {
    // 1) If a container was passed, tell Slim to use it
    if ($container !== null) {
        AppFactory::setContainer($container);
    }

    // 2) Instantiate the app
    $app = AppFactory::create();

    // 3) Middleware
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $errorMw = $app->addErrorMiddleware(false, true, true);
    $errorMw->getDefaultErrorHandler()->forceContentType('application/json');

    // 4) Routes
    $app->get('/hello_world',   [\App\Controllers\HelloController::class, 'helloWorld']);
    $app->post('/user/create',  [\App\Controllers\UserController::class,     'create']);
    $app->post('/group/{id}/join', [\App\Controllers\UserController::class,  'join']);
    $app->post('/group/create', [\App\Controllers\GroupController::class,    'create']);
    $app->post('/group/{id}/send',   [\App\Controllers\MessageController::class, 'send']);
    $app->get('/group/{id}/messages/new', [\App\Controllers\MessageController::class, 'retrieveNew']);
    $app->get('/group/{id}/messages/all', [\App\Controllers\MessageController::class, 'retrieveAll']);

    return $app;
};
