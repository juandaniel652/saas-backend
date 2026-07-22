<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Services\Controllers\ServiceController;

return function (Router $router): void {
    $router->get(
        '/api/v1/services',
        [ServiceController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['services.view'])],
    );

    $router->post(
        '/api/v1/services',
        [ServiceController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['services.manage'])],
    );
};