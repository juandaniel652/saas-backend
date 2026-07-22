<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Exceptions\AppException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use Closure;

final class PermissionMiddleware implements MiddlewareInterface
{
    /** @param string[] $requiredPermissions */
    public function __construct(private readonly array $requiredPermissions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var AuthenticatedUser|null $auth */
        $auth = $request->attribute('auth');

        if ($auth === null || !$auth->hasAnyPermission($this->requiredPermissions)) {
            throw new class ('No tenes permisos para realizar esta accion', 403) extends AppException {
                public function __construct(string $message, int $status)
                {
                    parent::__construct($message, $status);
                }
            };
        }

        return $next($request);
    }
}