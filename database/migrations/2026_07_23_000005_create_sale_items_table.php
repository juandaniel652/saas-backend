<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS sale_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sale_id INT NOT NULL,
                item_type VARCHAR(20) NOT NULL COMMENT "product o service",
                item_id INT NOT NULL,
                item_name VARCHAR(255) NOT NULL COMMENT "snapshot del nombre al momento de la venta",
                quantity INT NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                CONSTRAINT fk_saleitems_sale FOREIGN KEY (sale_id) REFERENCES sales(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS sale_items');
    },
];