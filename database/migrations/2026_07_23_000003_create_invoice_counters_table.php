<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS invoice_counters (
                company_id INT NOT NULL PRIMARY KEY,
                next_number INT NOT NULL DEFAULT 1,
                CONSTRAINT fk_invoicecounter_company FOREIGN KEY (company_id) REFERENCES companies(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS invoice_counters');
    },
];