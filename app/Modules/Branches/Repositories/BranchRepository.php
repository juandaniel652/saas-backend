<?php

declare(strict_types=1);

namespace App\Modules\Branches\Repositories;

use App\Core\Database\Connection;

final class BranchRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare('SELECT * FROM branches WHERE company_id = :company_id');
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    public function create(int $companyId, string $name, ?string $address): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO branches (company_id, name, address, created_at) VALUES (:company_id, :name, :address, NOW())',
        );
        $stmt->execute(['company_id' => $companyId, 'name' => $name, 'address' => $address]);

        return (int) $this->connection->pdo()->lastInsertId();
    }
}