<?php

declare(strict_types=1);

namespace App\Modules\Users\Repositories;

use App\Core\Database\Connection;

final class UserRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<string, mixed>|null */
    public function findByEmailAndCompany(string $email, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM users WHERE email = :email AND company_id = :company_id',
        );
        $stmt->execute(['email' => $email, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public function findByEmailAcrossCompanies(string $email): array
    {
        $stmt = $this->connection->pdo()->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->pdo()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(int $companyId, string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO users (company_id, name, email, password_hash, created_at)
             VALUES (:company_id, :name, :email, :password_hash, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    /** @return string[] roles y permissions consolidados del usuario */
    public function rolesForUser(int $userId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT r.slug FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :user_id',
        );
        $stmt->execute(['user_id' => $userId]);

        return array_column($stmt->fetchAll(), 'slug');
    }

    /** @return string[] */
    public function permissionsForUser(int $userId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT DISTINCT p.slug FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = :user_id',
        );
        $stmt->execute(['user_id' => $userId]);

        return array_column($stmt->fetchAll(), 'slug');
    }

    public function attachRole(int $userId, int $roleId): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)',
        );
        $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }
}