<?php

declare(strict_types=1);

namespace App\Modules\Sales\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
}