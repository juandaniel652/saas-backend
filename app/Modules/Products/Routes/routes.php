<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Products\Controllers\ProductController;

return function (Router $router): void {
    $router->get(
        '/api/v1/products',
        [ProductController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['products.view'])],
    );

    $router->get(
        '/api/v1/products/{id}',
        [ProductController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['products.view'])],
    );

    $router->post(
        '/api/v1/products',
        [ProductController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['products.manage'])],
    );

    $router->post(
        '/api/v1/products/{id}/stock-adjustments',
        [ProductController::class, 'adjustStock'],
        [AuthMiddleware::class, new PermissionMiddleware(['stock.manage'])],
    );

    $router->get(
        '/api/v1/products/{id}/stock-movements',
        [ProductController::class, 'stockHistory'],
        [AuthMiddleware::class, new PermissionMiddleware(['products.view'])],
    );
};