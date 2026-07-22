<?php

declare(strict_types=1);

namespace App\Modules\Branches\DTO;

final class CreateBranchDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $address,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            address: isset($data['address']) ? (string) $data['address'] : null,
        );
    }
}