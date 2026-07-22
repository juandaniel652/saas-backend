<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Router\Router;
use App\Modules\Auth\Controllers\AuthController;

return function (Router $router): void {
    $router->post('/api/v1/auth/register', [AuthController::class, 'register']);
    $router->post('/api/v1/auth/login', [AuthController::class, 'login']);
    $router->get('/api/v1/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);
};