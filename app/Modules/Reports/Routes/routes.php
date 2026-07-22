<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Reports\Controllers\ReportController;

return function (Router $router): void {
    $router->get(
        '/api/v1/reports/sales-summary',
        [ReportController::class, 'salesSummary'],
        [AuthMiddleware::class, new PermissionMiddleware(['reports.view'])],
    );

    $router->get(
        '/api/v1/reports/appointments-summary',
        [ReportController::class, 'appointmentsSummary'],
        [AuthMiddleware::class, new PermissionMiddleware(['reports.view'])],
    );

    $router->get(
        '/api/v1/reports/top-services',
        [ReportController::class, 'topServices'],
        [AuthMiddleware::class, new PermissionMiddleware(['reports.view'])],
    );
};