<?php

declare(strict_types=1);

namespace App\Modules\Services\DTO;

final class ServiceCatalogItemDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $durationMinutes,
        public readonly float $price,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            description: isset($data['description']) && $data['description'] !== ''
                ? (string) $data['description']
                : null,
            durationMinutes: (int) $data['duration_minutes'],
            price: (float) $data['price'],
        );
    }
}