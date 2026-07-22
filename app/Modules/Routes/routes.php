<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Router\Router;
use App\Modules\Companies\Controllers\CompanyController;

return function (Router $router): void {
    $router->get('/api/v1/companies/me', [CompanyController::class, 'me'], [AuthMiddleware::class]);
};