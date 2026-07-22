<?php

declare(strict_types=1);

namespace App\Modules\Audit\Listeners;

use App\Core\Events\EventInterface;
use App\Core\Events\ListenerInterface;
use App\Modules\Audit\Repositories\AuditRepository;
use App\Modules\Sales\Events\SaleCreated;

final class AuditSaleCreated implements ListenerInterface
{
    public function __construct(private readonly AuditRepository $audit)
    {
    }

    public function handle(EventInterface $event): void
    {
        if (!$event instanceof SaleCreated) {
            return;
        }

        $this->audit->record(
            companyId: $event->companyId,
            userId: $event->userId,
            action: 'sale.created',
            entityType: 'sale',
            entityId: $event->saleId,
            metadata: ['invoice_number' => $event->invoiceNumber, 'total' => $event->total],
        );
    }
}