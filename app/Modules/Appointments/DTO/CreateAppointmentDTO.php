<?php

declare(strict_types=1);

namespace App\Modules\Appointments\DTO;

final class CreateAppointmentDTO
{
    public function __construct(
        public readonly int $branchId,
        public readonly int $clientId,
        public readonly int $employeeId,
        public readonly int $serviceId,
        public readonly string $startsAt,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            branchId: (int) $data['branch_id'],
            clientId: (int) $data['client_id'],
            employeeId: (int) $data['employee_id'],
            serviceId: (int) $data['service_id'],
            startsAt: (string) $data['starts_at'],
            notes: isset($data['notes']) && $data['notes'] !== '' ? (string) $data['notes'] : null,
        );
    }
}