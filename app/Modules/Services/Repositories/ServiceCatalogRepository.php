<?php

declare(strict_types=1);

namespace App\Modules\Services\Repositories;

use App\Core\Database\Connection;

final class ServiceCatalogRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM services WHERE company_id = :company_id ORDER BY name',
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM services WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(int $companyId, string $name, ?string $description, int $durationMinutes, float $price): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO services (company_id, name, description, duration_minutes, price, created_at)
             VALUES (:company_id, :name, :description, :duration_minutes, :price, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'name' => $name,
            'description' => $description,
            'duration_minutes' => $durationMinutes,
            'price' => $price,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }
}