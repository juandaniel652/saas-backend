<?php

declare(strict_types=1);

namespace App\Modules\Employees\DTO;

final class EmployeeDTO
{
    /** @param int[] $serviceIds */
    public function __construct(
        public readonly int $branchId,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly array $serviceIds,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            branchId: (int) $data['branch_id'],
            name: (string) $data['name'],
            email: isset($data['email']) && $data['email'] !== '' ? (string) $data['email'] : null,
            phone: isset($data['phone']) && $data['phone'] !== '' ? (string) $data['phone'] : null,
            serviceIds: array_map('intval', $data['service_ids'] ?? []),
        );
    }
}