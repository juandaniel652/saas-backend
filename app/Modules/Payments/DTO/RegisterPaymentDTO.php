<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTO;

final class RegisterPaymentDTO
{
    public function __construct(
        public readonly float $amount,
        public readonly string $method,
        public readonly ?string $paidAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) $data['amount'],
            method: (string) $data['method'],
            paidAt: isset($data['paid_at']) && $data['paid_at'] !== '' ? (string) $data['paid_at'] : null,
        );
    }
}