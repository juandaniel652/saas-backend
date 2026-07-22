<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Listeners;

use App\Core\Events\EventInterface;
use App\Core\Events\ListenerInterface;
use App\Core\Logging\Logger;
use App\Modules\Appointments\Events\AppointmentCreated;
use App\Modules\Clients\Repositories\ClientRepository;
use App\Modules\Employees\Repositories\EmployeeRepository;
use App\Modules\Notifications\Factories\NotificationChannelFactory;
use App\Modules\Services\Repositories\ServiceCatalogRepository;

final class SendAppointmentConfirmationEmail implements ListenerInterface
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly EmployeeRepository $employees,
        private readonly ServiceCatalogRepository $services,
        private readonly NotificationChannelFactory $channels,
        private readonly Logger $logger,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        if (!$event instanceof AppointmentCreated) {
            return;
        }

        $client = $this->clients->findByIdAndCompany($event->clientId, $event->companyId);

        if ($client === null || $client['email'] === null) {
            $this->logger->info('No se envio confirmacion: cliente sin email', ['appointment_id' => $event->appointmentId]);

            return;
        }

        $employee = $this->employees->findByIdAndCompany($event->employeeId, $event->companyId);
        $service = $this->services->findByIdAndCompany($event->serviceId, $event->companyId);

        $subject = 'Confirmacion de tu turno';
        $body = sprintf(
            '<p>Hola %s,</p><p>Tu turno para <strong>%s</strong> con %s quedo confirmado para el %s.</p>',
            htmlspecialchars($client['name']),
            htmlspecialchars($service['name'] ?? 'el servicio solicitado'),
            htmlspecialchars($employee['name'] ?? 'nuestro equipo'),
            htmlspecialchars($event->startsAt),
        );

        $this->channels->make('email')->send($client['email'], $subject, $body);
    }
}