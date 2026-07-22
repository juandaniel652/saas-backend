<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Auth\JwtManager;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use Closure;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly JwtManager $jwtManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if ($header === null || !str_starts_with($header, 'Bearer ')) {
            throw new UnauthorizedException('Falta el token de autenticacion');
        }

        $token = substr($header, 7);
        $claims = $this->jwtManager->decode($token);

        if (($claims['type'] ?? null) !== 'access') {
            throw new UnauthorizedException('Tipo de token invalido');
        }

        $authenticatedUser = new AuthenticatedUser(
            userId: (int) $claims['sub'],
            companyId: (int) $claims['company_id'],
            roles: (array) $claims['roles'],
            permissions: (array) $claims['permissions'],
        );

        $request = $request->withAttribute('auth', $authenticatedUser);

        return $next($request);
    }
}