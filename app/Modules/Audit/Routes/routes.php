<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Audit\Controllers\AuditController;

return function (Router $router): void {
    $router->get(
        '/api/v1/audit-logs',
        [AuditController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['audit.view'])],
    );
};