<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sale_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                method VARCHAR(30) NOT NULL,
                paid_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_payments_sale FOREIGN KEY (sale_id) REFERENCES sales(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS payments');
    },
];