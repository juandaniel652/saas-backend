<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class TimeRange
{
    public function __construct(
        public readonly DateTimeImmutable $start,
        public readonly DateTimeImmutable $end,
    ) {
        if ($this->end <= $this->start) {
            throw new InvalidArgumentException('La fecha de fin debe ser posterior a la de inicio');
        }
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $this->end > $other->start;
    }
}