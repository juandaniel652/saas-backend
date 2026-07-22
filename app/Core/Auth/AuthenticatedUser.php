<?php

declare(strict_types=1);

namespace App\Core\Auth;

final class AuthenticatedUser
{
    /**
     * @param string[] $roles
     * @param string[] $permissions
     */
    public function __construct(
        public readonly int $userId,
        public readonly int $companyId,
        public readonly array $roles,
        public readonly array $permissions,
    ) {
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return array_intersect($permissions, $this->permissions) !== [];
    }
}