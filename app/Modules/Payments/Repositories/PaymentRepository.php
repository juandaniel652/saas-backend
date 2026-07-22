<?php

declare(strict_types=1);

namespace App\Modules\Payments\Repositories;

use App\Core\Database\Connection;

final class PaymentRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findBySale(int $saleId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM payments WHERE sale_id = :sale_id ORDER BY paid_at',
        );
        $stmt->execute(['sale_id' => $saleId]);

        return $stmt->fetchAll();
    }

    public function totalPaidForSale(int $saleId): float
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE sale_id = :sale_id',
        );
        $stmt->execute(['sale_id' => $saleId]);

        return (float) $stmt->fetchColumn();
    }

    public function create(int $saleId, float $amount, string $method, string $paidAt): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO payments (sale_id, amount, method, paid_at, created_at)
             VALUES (:sale_id, :amount, :method, :paid_at, NOW())',
        );
        $stmt->execute([
            'sale_id' => $saleId,
            'amount' => $amount,
            'method' => $method,
            'paid_at' => $paidAt,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }
}