<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Roles\Controllers\RoleController;

return function (Router $router): void {
    $router->get(
        '/api/v1/permissions',
        [RoleController::class, 'permissionsCatalog'],
        [AuthMiddleware::class, new PermissionMiddleware(['roles.manage'])],
    );

    $router->get(
        '/api/v1/roles',
        [RoleController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['roles.manage'])],
    );

    $router->get(
        '/api/v1/roles/{id}',
        [RoleController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['roles.manage'])],
    );

    $router->post(
        '/api/v1/roles',
        [RoleController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['roles.manage'])],
    );

    $router->put(
        '/api/v1/roles/{id}/permissions',
        [RoleController::class, 'updatePermissions'],
        [AuthMiddleware::class, new PermissionMiddleware(['roles.manage'])],
    );
};