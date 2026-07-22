<?php

declare(strict_types=1);

namespace App\Modules\Payments\Events;

use App\Core\Events\EventInterface;

final class PaymentRegistered implements EventInterface
{
    public function __construct(
        public readonly int $paymentId,
        public readonly int $saleId,
        public readonly int $companyId,
        public readonly ?int $userId,
        public readonly float $amount,
        public readonly string $method,
    ) {
    }
}