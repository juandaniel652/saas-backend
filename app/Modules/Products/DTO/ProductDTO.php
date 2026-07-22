<?php

declare(strict_types=1);

namespace App\Modules\Products\DTO;

final class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $sku,
        public readonly float $price,
        public readonly int $initialStock,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            sku: isset($data['sku']) && $data['sku'] !== '' ? (string) $data['sku'] : null,
            price: (float) $data['price'],
            initialStock: (int) ($data['initial_stock'] ?? 0),
        );
    }
}