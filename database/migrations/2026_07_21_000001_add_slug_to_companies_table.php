<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec('ALTER TABLE companies ADD COLUMN slug VARCHAR(255) NOT NULL UNIQUE AFTER name');
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('ALTER TABLE companies DROP COLUMN slug');
    },
];