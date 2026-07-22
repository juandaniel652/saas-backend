<?php

declare(strict_types=1);

namespace App\Modules\Reports\Repositories;

use App\Core\Database\Connection;

final class ReportRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<string, mixed> */
    public function salesSummary(int $companyId, string $from, string $to): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT
                COUNT(*) AS total_sales,
                COALESCE(SUM(total), 0) AS total_revenue
             FROM sales
             WHERE company_id = :company_id
               AND status = "completed"
               AND DATE(created_at) BETWEEN :from AND :to',
        );
        $stmt->execute(['company_id' => $companyId, 'from' => $from, 'to' => $to]);
        $sales = $stmt->fetch();

        $paymentsStmt = $this->connection->pdo()->prepare(
            'SELECT COALESCE(SUM(p.amount), 0) AS total_collected
             FROM payments p
             INNER JOIN sales s ON s.id = p.sale_id
             WHERE s.company_id = :company_id
               AND DATE(p.paid_at) BETWEEN :from AND :to',
        );
        $paymentsStmt->execute(['company_id' => $companyId, 'from' => $from, 'to' => $to]);
        $payments = $paymentsStmt->fetch();

        return [
            'total_sales' => (int) $sales['total_sales'],
            'total_revenue' => (float) $sales['total_revenue'],
            'total_collected' => (float) $payments['total_collected'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function appointmentsByStatus(int $companyId, string $from, string $to): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT status, COUNT(*) AS total
             FROM appointments
             WHERE company_id = :company_id
               AND DATE(starts_at) BETWEEN :from AND :to
             GROUP BY status',
        );
        $stmt->execute(['company_id' => $companyId, 'from' => $from, 'to' => $to]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function topServices(int $companyId, string $from, string $to, int $limit): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT si.item_name, COUNT(*) AS times_sold, SUM(si.subtotal) AS revenue
             FROM sale_items si
             INNER JOIN sales s ON s.id = si.sale_id
             WHERE s.company_id = :company_id
               AND si.item_type = "service"
               AND s.status = "completed"
               AND DATE(s.created_at) BETWEEN :from AND :to
             GROUP BY si.item_name
             ORDER BY times_sold DESC
             LIMIT :limit',
        );
        $stmt->bindValue('company_id', $companyId, \PDO::PARAM_INT);
        $stmt->bindValue('from', $from);
        $stmt->bindValue('to', $to);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}