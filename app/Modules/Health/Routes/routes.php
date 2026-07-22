<?php

declare(strict_types=1);

use App\Core\Router\Router;
use App\Modules\Health\Controllers\HealthController;

return function (Router $router): void {
    $router->get('/api/v1/health', [HealthController::class, 'ping']);
};