<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

final class RegisterCompanyDTO
{
    public function __construct(
        public readonly string $companyName,
        public readonly string $ownerName,
        public readonly string $ownerEmail,
        public readonly string $ownerPassword,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            companyName: (string) $data['company_name'],
            ownerName: (string) $data['owner_name'],
            ownerEmail: (string) $data['owner_email'],
            ownerPassword: (string) $data['owner_password'],
        );
    }
}