<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS stock_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                product_id INT NOT NULL,
                type VARCHAR(10) NOT NULL COMMENT "in o out",
                quantity INT NOT NULL,
                reason VARCHAR(255) NULL,
                reference_type VARCHAR(50) NULL COMMENT "ej: sale, manual_adjustment",
                reference_id INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_stockmov_company FOREIGN KEY (company_id) REFERENCES companies(id),
                CONSTRAINT fk_stockmov_product FOREIGN KEY (product_id) REFERENCES products(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS stock_movements');
    },
];