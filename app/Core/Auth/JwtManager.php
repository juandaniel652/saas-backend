<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Config\Config;
use App\Core\Exceptions\UnauthorizedException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

final class JwtManager
{
    public function __construct(private readonly Config $config)
    {
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function issueAccessToken(array $claims): string
    {
        $now = time();
        $ttl = (int) $this->config->get('jwt.ttl', 3600);

        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttl,
            'type' => 'access',
        ]);

        return JWT::encode($payload, (string) $this->config->get('jwt.secret'), 'HS256');
    }

    /** @return array<string, mixed> */
    public function decode(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key((string) $this->config->get('jwt.secret'), 'HS256'));

            return (array) $decoded;
        } catch (ExpiredException) {
            throw new UnauthorizedException('El token ha expirado');
        } catch (SignatureInvalidException | UnexpectedValueException) {
            throw new UnauthorizedException('Token invalido');
        }
    }

    public function ttlSeconds(): int
    {
        return (int) $this->config->get('jwt.ttl', 3600);
    }

    public function refreshTtlSeconds(): int
    {
        return (int) $this->config->get('jwt.refresh_ttl', 1209600);
    }
}