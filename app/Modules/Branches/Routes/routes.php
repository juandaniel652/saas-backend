<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Branches\Controllers\BranchController;

return function (Router $router): void {
    $router->get(
        '/api/v1/branches',
        [BranchController::class, 'index'],
        [AuthMiddleware::class, new PermissionMiddleware(['branches.view'])],
    );

    $router->post(
        '/api/v1/branches',
        [BranchController::class, 'store'],
        [AuthMiddleware::class, new PermissionMiddleware(['branches.create'])],
    );
};