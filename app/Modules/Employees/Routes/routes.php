<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Employees\Controllers\EmployeeController;

return function (Router $router): void {
    $router->get(
        '/api/v1/employees',
        [EmployeeController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['employees.view'])],
    );

    $router->get(
        '/api/v1/employees/{id}',
        [EmployeeController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['employees.view'])],
    );

    $router->post(
        '/api/v1/employees',
        [EmployeeController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['employees.manage'])],
    );

    $router->get(
        '/api/v1/employees/{id}/services',
        [EmployeeController::class, 'services'],
        [AuthMiddleware::class, new PermissionMiddleware(['employees.view'])],
    );

    $router->put(
        '/api/v1/employees/{id}/services',
        [EmployeeController::class, 'assignServices'],
        [AuthMiddleware::class, new PermissionMiddleware(['employees.manage'])],
    );
};