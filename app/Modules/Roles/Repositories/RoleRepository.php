<?php

declare(strict_types=1);

namespace App\Modules\Roles\Repositories;

use App\Core\Database\Connection;

final class RoleRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM roles WHERE company_id = :company_id ORDER BY name',
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM roles WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function belongsToCompany(int $roleId, int $companyId): bool
    {
        return $this->findByIdAndCompany($roleId, $companyId) !== null;
    }

    public function slugExists(int $companyId, string $slug): bool
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT 1 FROM roles WHERE company_id = :company_id AND slug = :slug',
        );
        $stmt->execute(['company_id' => $companyId, 'slug' => $slug]);

        return $stmt->fetch() !== false;
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

    /** @return array<int, array<string, mixed>> */
    public function permissionsForRole(int $roleId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT p.* FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = :role_id',
        );
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll();
    }

    /** @param int[] $permissionIds */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $pdo = $this->connection->pdo();

        $delete = $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
        $delete->execute(['role_id' => $roleId]);

        $insert = $pdo->prepare(
            'INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)',
        );

        foreach ($permissionIds as $permissionId) {
            $insert->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }
}