<?php

declare(strict_types=1);

namespace App\Modules\Users\DTO;

final class InviteUserDTO
{
    /** @param int[] $roleIds */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly array $roleIds,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            email: (string) $data['email'],
            password: (string) $data['password'],
            roleIds: array_map('intval', $data['role_ids'] ?? []),
        );
    }
}