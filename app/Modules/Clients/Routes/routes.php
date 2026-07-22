<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Clients\Controllers\ClientController;

return function (Router $router): void {
    $router->get(
        '/api/v1/clients',
        [ClientController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['clients.view'])],
    );

    $router->get(
        '/api/v1/clients/{id}',
        [ClientController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['clients.view'])],
    );

    $router->post(
        '/api/v1/clients',
        [ClientController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['clients.manage'])],
    );

    $router->put(
        '/api/v1/clients/{id}',
        [ClientController::class, 'update'],
        [AuthMiddleware::class, new PermissionMiddleware(['clients.manage'])],
    );
};