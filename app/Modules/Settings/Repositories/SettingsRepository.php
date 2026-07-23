<?php

declare(strict_types=1);

namespace App\Modules\Settings\Repositories;

use App\Core\Database\Connection;

final class SettingsRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<string, string> */
    public function allForCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT setting_key, setting_value FROM company_settings WHERE company_id = :company_id',
        );
        $stmt->execute(['company_id' => $companyId]);

        $result = [];

        foreach ($stmt->fetchAll() as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }

        return $result;
    }

    public function get(int $companyId, string $key): ?string
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT setting_value FROM company_settings WHERE company_id = :company_id AND setting_key = :key',
        );
        $stmt->execute(['company_id' => $companyId, 'key' => $key]);

        $value = $stmt->fetchColumn();

        return $value === false ? null : (string) $value;
    }

    public function set(int $companyId, string $key, string $value): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO company_settings (company_id, setting_key, setting_value)
             VALUES (:company_id, :key, :value)
             ON DUPLICATE KEY UPDATE setting_value = :value',
        );
        $stmt->execute(['company_id' => $companyId, 'key' => $key, 'value' => $value]);
    }
}