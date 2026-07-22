<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS employee_services (
                employee_id INT NOT NULL,
                service_id INT NOT NULL,
                PRIMARY KEY (employee_id, service_id),
                CONSTRAINT fk_es_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
                CONSTRAINT fk_es_service FOREIGN KEY (service_id) REFERENCES services(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS employee_services');
    },
];