<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Users\Controllers\UserController;

return function (Router $router): void {
    $router->get(
        '/api/v1/users',
        [UserController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['users.view'])],
    );

    $router->get(
        '/api/v1/users/{id}',
        [UserController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['users.view'])],
    );

    $router->post(
        '/api/v1/users',
        [UserController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['users.manage'])],
    );

    $router->put(
        '/api/v1/users/{id}/roles',
        [UserController::class, 'updateRoles'],
        [AuthMiddleware::class, new PermissionMiddleware(['users.manage'])],
    );
};