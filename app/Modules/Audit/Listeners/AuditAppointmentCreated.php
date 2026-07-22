<?php

declare(strict_types=1);

namespace App\Modules\Audit\Listeners;

use App\Core\Events\EventInterface;
use App\Core\Events\ListenerInterface;
use App\Modules\Appointments\Events\AppointmentCreated;
use App\Modules\Audit\Repositories\AuditRepository;

final class AuditAppointmentCreated implements ListenerInterface
{
    public function __construct(private readonly AuditRepository $audit)
    {
    }

    public function handle(EventInterface $event): void
    {
        if (!$event instanceof AppointmentCreated) {
            return;
        }

        $this->audit->record(
            companyId: $event->companyId,
            userId: $event->userId,
            action: 'appointment.created',
            entityType: 'appointment',
            entityId: $event->appointmentId,
            metadata: ['starts_at' => $event->startsAt, 'employee_id' => $event->employeeId],
        );
    }
}