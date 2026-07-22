<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class NotFoundException extends AppException
{
    public function __construct(string $message = 'Recurso no encontrado')
    {
        parent::__construct($message, 404);
    }
}