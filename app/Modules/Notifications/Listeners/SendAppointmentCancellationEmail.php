<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Listeners;

use App\Core\Events\EventInterface;
use App\Core\Events\ListenerInterface;
use App\Modules\Appointments\Events\AppointmentCancelled;
use App\Modules\Clients\Repositories\ClientRepository;
use App\Modules\Notifications\Factories\NotificationChannelFactory;

final class SendAppointmentCancellationEmail implements ListenerInterface
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly NotificationChannelFactory $channels,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        if (!$event instanceof AppointmentCancelled) {
            return;
        }

        $client = $this->clients->findByIdAndCompany($event->clientId, $event->companyId);

        if ($client === null || $client['email'] === null) {
            return;
        }

        $this->channels->make('email')->send(
            $client['email'],
            'Tu turno fue cancelado',
            '<p>Hola ' . htmlspecialchars($client['name']) . ', tu turno fue cancelado. Si fue un error, contactanos para reagendar.</p>',
        );
    }
}