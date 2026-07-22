<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Appointments\Controllers\AppointmentController;

return function (Router $router): void {
    $router->get(
        '/api/v1/appointments',
        [AppointmentController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['appointments.view'])],
    );

    $router->get(
        '/api/v1/appointments/{id}',
        [AppointmentController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['appointments.view'])],
    );

    $router->post(
        '/api/v1/appointments',
        [AppointmentController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['appointments.manage'])],
    );

    $router->post(
        '/api/v1/appointments/{id}/cancel',
        [AppointmentController::class, 'cancel'],
        [AuthMiddleware::class, new PermissionMiddleware(['appointments.cancel'])],
    );
};