<?php

declare(strict_types=1);

namespace App\Modules\Sales\Enums;

enum SaleStatus: string
{
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}