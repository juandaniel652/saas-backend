<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Repositories;

use App\Core\Database\Connection;

final class AppointmentRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId, ?int $employeeId = null, ?string $date = null): array
    {
        $sql = 'SELECT * FROM appointments WHERE company_id = :company_id';
        $bindings = ['company_id' => $companyId];

        if ($employeeId !== null) {
            $sql .= ' AND employee_id = :employee_id';
            $bindings['employee_id'] = $employeeId;
        }

        if ($date !== null) {
            $sql .= ' AND DATE(starts_at) = :date';
            $bindings['date'] = $date;
        }

        $sql .= ' ORDER BY starts_at';

        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM appointments WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function hasOverlap(int $employeeId, string $startsAt, string $endsAt): bool
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT 1 FROM appointments
             WHERE employee_id = :employee_id
               AND status != "cancelled"
               AND starts_at < :ends_at
               AND ends_at > :starts_at
             LIMIT 1',
        );
        $stmt->execute([
            'employee_id' => $employeeId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return $stmt->fetch() !== false;
    }

    public function create(
        int $companyId,
        int $branchId,
        int $clientId,
        int $employeeId,
        int $serviceId,
        string $startsAt,
        string $endsAt,
        string $status,
        ?string $notes,
    ): int {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO appointments
                (company_id, branch_id, client_id, employee_id, service_id, starts_at, ends_at, status, notes, created_at)
             VALUES
                (:company_id, :branch_id, :client_id, :employee_id, :service_id, :starts_at, :ends_at, :status, :notes, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'client_id' => $clientId,
            'employee_id' => $employeeId,
            'service_id' => $serviceId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $status,
            'notes' => $notes,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->connection->pdo()->prepare('UPDATE appointments SET status = :status WHERE id = :id');
        $stmt->execute(['id' => $id, 'status' => $status]);
    }
}