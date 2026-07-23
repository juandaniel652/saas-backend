<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS company_settings (
                company_id INT NOT NULL,
                setting_key VARCHAR(100) NOT NULL,
                setting_value VARCHAR(500) NULL,
                PRIMARY KEY (company_id, setting_key),
                CONSTRAINT fk_settings_company FOREIGN KEY (company_id) REFERENCES companies(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS company_settings');
    },
];