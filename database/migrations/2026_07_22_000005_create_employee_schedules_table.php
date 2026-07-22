<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS employee_schedules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id INT NOT NULL,
                weekday TINYINT NOT NULL COMMENT "1=lunes ... 7=domingo (ISO-8601)",
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                CONSTRAINT fk_schedule_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
                CONSTRAINT chk_schedule_weekday CHECK (weekday BETWEEN 1 AND 7)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS employee_schedules');
    },
];