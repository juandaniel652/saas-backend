<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class ValidationException extends AppException
{
    /** @param array<string, string[]> $errors */
    public function __construct(array $errors, string $message = 'Los datos enviados no son validos')
    {
        parent::__construct($message, 422, $errors);
    }
}