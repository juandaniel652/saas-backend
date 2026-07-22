<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Events;

use App\Core\Events\EventInterface;

final class AppointmentCreated implements EventInterface
{
    public function __construct(
        public readonly int $appointmentId,
        public readonly int $companyId,
        public readonly ?int $userId,
        public readonly int $clientId,
        public readonly int $employeeId,
        public readonly int $serviceId,
        public readonly string $startsAt,
    ) {
    }
}