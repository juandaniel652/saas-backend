<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS sales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                branch_id INT NOT NULL,
                client_id INT NULL,
                appointment_id INT NULL,
                invoice_number INT NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT "completed",
                payment_status VARCHAR(20) NOT NULL DEFAULT "unpaid",
                notes VARCHAR(500) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_sales_company FOREIGN KEY (company_id) REFERENCES companies(id),
                CONSTRAINT fk_sales_branch FOREIGN KEY (branch_id) REFERENCES branches(id),
                CONSTRAINT fk_sales_client FOREIGN KEY (client_id) REFERENCES clients(id),
                CONSTRAINT fk_sales_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id),
                CONSTRAINT uq_sales_company_invoice UNIQUE (company_id, invoice_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS sales');
    },
];