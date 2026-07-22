<?php

declare(strict_types=1);

namespace App\Modules\Employees\Repositories;

use App\Core\Database\Connection;

final class EmployeeRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM employees WHERE company_id = :company_id ORDER BY name',
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM employees WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(int $companyId, int $branchId, string $name, ?string $email, ?string $phone): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO employees (company_id, branch_id, name, email, phone, created_at)
             VALUES (:company_id, :branch_id, :name, :email, :phone, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    /** @param int[] $serviceIds */
    public function syncServices(int $employeeId, array $serviceIds): void
    {
        $pdo = $this->connection->pdo();

        $delete = $pdo->prepare('DELETE FROM employee_services WHERE employee_id = :employee_id');
        $delete->execute(['employee_id' => $employeeId]);

        $insert = $pdo->prepare(
            'INSERT INTO employee_services (employee_id, service_id) VALUES (:employee_id, :service_id)',
        );

        foreach ($serviceIds as $serviceId) {
            $insert->execute(['employee_id' => $employeeId, 'service_id' => $serviceId]);
        }
    }

    public function performsService(int $employeeId, int $serviceId): bool
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT 1 FROM employee_services WHERE employee_id = :employee_id AND service_id = :service_id',
        );
        $stmt->execute(['employee_id' => $employeeId, 'service_id' => $serviceId]);

        return $stmt->fetch() !== false;
    }

    /** @return array<int, array<string, mixed>> */
    public function servicesForEmployee(int $employeeId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT s.* FROM services s
             INNER JOIN employee_services es ON es.service_id = s.id
             WHERE es.employee_id = :employee_id',
        );
        $stmt->execute(['employee_id' => $employeeId]);

        return $stmt->fetchAll();
    }
}