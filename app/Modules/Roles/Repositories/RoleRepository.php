<?php

declare(strict_types=1);

namespace App\Modules\Roles\Repositories;

use App\Core\Database\Connection;

final class RoleRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function create(int $companyId, string $name, string $slug): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO roles (company_id, name, slug, created_at) VALUES (:company_id, :name, :slug, NOW())',
        );
        $stmt->execute(['company_id' => $companyId, 'name' => $name, 'slug' => $slug]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    public function attachPermission(int $roleId, int $permissionId): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)',
        );
        $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
    }
}