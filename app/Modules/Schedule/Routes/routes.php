<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Schedule\Controllers\ScheduleController;

return function (Router $router): void {
    $router->get(
        '/api/v1/employees/{id}/schedule',
        [ScheduleController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['schedule.view'])],
    );

    $router->put(
        '/api/v1/employees/{id}/schedule',
        [ScheduleController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['schedule.manage'])],
    );
};