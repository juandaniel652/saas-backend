<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                sku VARCHAR(100) NULL,
                price DECIMAL(10,2) NOT NULL,
                stock_quantity INT NOT NULL DEFAULT 0,
                active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_products_company FOREIGN KEY (company_id) REFERENCES companies(id),
                CONSTRAINT uq_products_company_sku UNIQUE (company_id, sku)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS products');
    },
];