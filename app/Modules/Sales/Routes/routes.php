<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Sales\Controllers\SaleController;

return function (Router $router): void {
    $router->get(
        '/api/v1/sales',
        [SaleController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['sales.view'])],
    );

    $router->get(
        '/api/v1/sales/{id}',
        [SaleController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['sales.view'])],
    );

    $router->post(
        '/api/v1/sales',
        [SaleController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['sales.manage'])],
    );

    $router->post(
        '/api/v1/sales/{id}/cancel',
        [SaleController::class, 'cancel'],
        [AuthMiddleware::class, new PermissionMiddleware(['sales.manage'])],
    );
};