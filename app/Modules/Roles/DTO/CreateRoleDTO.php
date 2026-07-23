<?php

declare(strict_types=1);

namespace App\Modules\Roles\DTO;

final class CreateRoleDTO
{
    /** @param string[] $permissionSlugs */
    public function __construct(
        public readonly string $name,
        public readonly array $permissionSlugs,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            permissionSlugs: array_map('strval', $data['permission_slugs'] ?? []),
        );
    }
}