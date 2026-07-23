<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Router\Router;
use App\Modules\Settings\Controllers\SettingsController;

return function (Router $router): void {
    $router->get(
        '/api/v1/settings',
        [SettingsController::class, 'show'],
        [AuthMiddleware::class, new PermissionMiddleware(['settings.view'])],
    );

    $router->put(
        '/api/v1/settings',
        [SettingsController::class, 'update'],
        [AuthMiddleware::class, new PermissionMiddleware(['settings.manage'])],
    );
};