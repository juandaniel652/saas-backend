<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class UnauthorizedException extends AppException
{
    public function __construct(string $message = 'No autenticado')
    {
        parent::__construct($message, 401);
    }
}