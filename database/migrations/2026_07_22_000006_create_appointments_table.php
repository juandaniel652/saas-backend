<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS appointments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                branch_id INT NOT NULL,
                client_id INT NOT NULL,
                employee_id INT NOT NULL,
                service_id INT NOT NULL,
                starts_at DATETIME NOT NULL,
                ends_at DATETIME NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT "confirmed",
                notes VARCHAR(500) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_appt_company FOREIGN KEY (company_id) REFERENCES companies(id),
                CONSTRAINT fk_appt_branch FOREIGN KEY (branch_id) REFERENCES branches(id),
                CONSTRAINT fk_appt_client FOREIGN KEY (client_id) REFERENCES clients(id),
                CONSTRAINT fk_appt_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
                CONSTRAINT fk_appt_service FOREIGN KEY (service_id) REFERENCES services(id),
                INDEX idx_appt_employee_time (employee_id, starts_at, ends_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS appointments');
    },
];