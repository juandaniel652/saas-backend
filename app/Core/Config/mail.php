<?php

declare(strict_types=1);

return [
    'driver' => $_ENV['MAIL_DRIVER'] ?? 'log',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Plataforma de Turnos',
    'host' => $_ENV['MAIL_HOST'] ?? '',
    'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
];