<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Repositories;

use App\Core\Database\Connection;

final class PermissionRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->connection->pdo()->query('SELECT * FROM permissions');

        return $stmt->fetchAll();
    }

    public function create(string $slug, ?string $description): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT IGNORE INTO permissions (slug, description) VALUES (:slug, :description)',
        );
        $stmt->execute(['slug' => $slug, 'description' => $description]);

        $existing = $this->connection->pdo()->prepare('SELECT id FROM permissions WHERE slug = :slug');
        $existing->execute(['slug' => $slug]);

        return (int) $existing->fetchColumn();
    }
}