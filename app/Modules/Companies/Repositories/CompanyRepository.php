<?php

declare(strict_types=1);

namespace App\Modules\Companies\Repositories;

use App\Core\Database\Connection;

final class CompanyRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->pdo()->prepare('SELECT * FROM companies WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->connection->pdo()->prepare('SELECT 1 FROM companies WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);

        return $stmt->fetch() !== false;
    }

    public function create(string $name, string $slug): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO companies (name, slug, created_at) VALUES (:name, :slug, NOW())',
        );
        $stmt->execute(['name' => $name, 'slug' => $slug]);

        return (int) $this->connection->pdo()->lastInsertId();
    }
}