<?php

declare(strict_types=1);

namespace App\Modules\Audit\Repositories;

use App\Core\Database\Connection;

final class AuditRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @param array<string, mixed> $metadata */
    public function record(int $companyId, ?int $userId, string $action, string $entityType, int $entityId, array $metadata = []): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO audit_logs (company_id, user_id, action, entity_type, entity_id, metadata, created_at)
             VALUES (:company_id, :user_id, :action, :entity_type, :entity_id, :metadata, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId, int $limit = 100): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM audit_logs WHERE company_id = :company_id ORDER BY created_at DESC LIMIT :limit',
        );
        $stmt->bindValue('company_id', $companyId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}