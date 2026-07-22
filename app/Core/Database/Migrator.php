<?php

declare(strict_types=1);

namespace App\Core\Database;

final class Migrator
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function run(string $migrationsPath): void
    {
        $this->ensureMigrationsTable();

        $applied = $this->appliedMigrations();
        $files = glob($migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.php');

            if (in_array($name, $applied, true)) {
                continue;
            }

            $migration = require $file;
            $migration['up']($this->connection->pdo());

            $stmt = $this->connection->pdo()->prepare(
                'INSERT INTO migrations (migration, applied_at) VALUES (:migration, NOW())',
            );
            $stmt->execute(['migration' => $name]);

            echo "Migrada: {$name}\n";
        }
    }

    private function ensureMigrationsTable(): void
    {
        $this->connection->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                applied_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    }

    /** @return string[] */
    private function appliedMigrations(): array
    {
        $stmt = $this->connection->pdo()->query('SELECT migration FROM migrations');

        return array_column($stmt->fetchAll(), 'migration');
    }
}