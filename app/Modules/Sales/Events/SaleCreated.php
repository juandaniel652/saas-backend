<?php

declare(strict_types=1);

namespace App\Modules\Sales\Events;

use App\Core\Events\EventInterface;

final class SaleCreated implements EventInterface
{
    public function __construct(
        public readonly int $saleId,
        public readonly int $companyId,
        public readonly ?int $userId,
        public readonly int $invoiceNumber,
        public readonly float $total,
    ) {
    }
}