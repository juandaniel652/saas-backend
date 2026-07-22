<?php

declare(strict_types=1);

namespace App\Modules\Audit\Listeners;

use App\Core\Events\EventInterface;
use App\Core\Events\ListenerInterface;
use App\Modules\Audit\Repositories\AuditRepository;
use App\Modules\Payments\Events\PaymentRegistered;

final class AuditPaymentRegistered implements ListenerInterface
{
    public function __construct(private readonly AuditRepository $audit)
    {
    }

    public function handle(EventInterface $event): void
    {
        if (!$event instanceof PaymentRegistered) {
            return;
        }

        $this->audit->record(
            companyId: $event->companyId,
            userId: $event->userId,
            action: 'payment.registered',
            entityType: 'payment',
            entityId: $event->paymentId,
            metadata: ['sale_id' => $event->saleId, 'amount' => $event->amount, 'method' => $event->method],
        );
    }
}