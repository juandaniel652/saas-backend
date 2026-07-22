<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_roles_company FOREIGN KEY (company_id) REFERENCES companies(id),
                CONSTRAINT uq_roles_company_slug UNIQUE (company_id, slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description VARCHAR(255) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS role_permissions (
                role_id INT NOT NULL,
                permission_id INT NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id),
                CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS user_roles (
                user_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (user_id, role_id),
                CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id),
                CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS user_roles');
        $pdo->exec('DROP TABLE IF EXISTS role_permissions');
        $pdo->exec('DROP TABLE IF EXISTS permissions');
        $pdo->exec('DROP TABLE IF EXISTS roles');
    },
];