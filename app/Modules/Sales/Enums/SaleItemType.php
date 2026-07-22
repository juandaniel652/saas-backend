<?php

declare(strict_types=1);

namespace App\Modules\Sales\Enums;

enum SaleItemType: string
{
    case Product = 'product';
    case Service = 'service';
}