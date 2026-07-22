<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

final class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly int $companyId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) $data['email'],
            password: (string) $data['password'],
            companyId: (int) $data['company_id'],
        );
    }
}