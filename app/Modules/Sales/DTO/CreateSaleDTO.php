<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTO;

final class CreateSaleDTO
{
    /** @param SaleItemInputDTO[] $items */
    public function __construct(
        public readonly int $branchId,
        public readonly ?int $clientId,
        public readonly ?int $appointmentId,
        public readonly array $items,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $items = array_map(
            static fn (array $item): SaleItemInputDTO => SaleItemInputDTO::fromArray($item),
            $data['items'] ?? [],
        );

        return new self(
            branchId: (int) $data['branch_id'],
            clientId: isset($data['client_id']) ? (int) $data['client_id'] : null,
            appointmentId: isset($data['appointment_id']) ? (int) $data['appointment_id'] : null,
            items: $items,
            notes: isset($data['notes']) && $data['notes'] !== '' ? (string) $data['notes'] : null,
        );
    }
}