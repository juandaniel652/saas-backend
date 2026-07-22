<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTO;

final class SaleItemInputDTO
{
    public function __construct(
        public readonly string $itemType,
        public readonly int $itemId,
        public readonly int $quantity,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            itemType: (string) $data['item_type'],
            itemId: (int) $data['item_id'],
            quantity: (int) $data['quantity'],
        );
    }
}