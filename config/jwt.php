<?php

declare(strict_types=1);

return [
    'secret' => $_ENV['JWT_SECRET'] ?? '',
    'ttl' => (int) ($_ENV['JWT_TTL'] ?? 3600),
    'refresh_ttl' => (int) ($_ENV['JWT_REFRESH_TTL'] ?? 1209600),
];