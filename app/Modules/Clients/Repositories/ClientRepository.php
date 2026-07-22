<?php

declare(strict_types=1);

namespace App\Modules\Clients\Repositories;

use App\Core\Database\Connection;

final class ClientRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM clients WHERE company_id = :company_id ORDER BY name',
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM clients WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(int $companyId, string $name, ?string $email, ?string $phone, ?string $notes): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO clients (company_id, name, email, phone, notes, created_at)
             VALUES (:company_id, :name, :email, :phone, :notes, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'notes' => $notes,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    public function update(int $id, string $name, ?string $email, ?string $phone, ?string $notes): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'UPDATE clients SET name = :name, email = :email, phone = :phone, notes = :notes WHERE id = :id',
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'notes' => $notes,
        ]);
    }
}