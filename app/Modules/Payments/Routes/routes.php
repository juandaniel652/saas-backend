<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Payments\Controllers\PaymentController;

return function (Router $router): void {
    $router->get(
        '/api/v1/sales/{saleId}/payments',
        [PaymentController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['sales.view'])],
    );

    $router->post(
        '/api/v1/sales/{saleId}/payments',
        [PaymentController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['payments.manage'])],
    );
};