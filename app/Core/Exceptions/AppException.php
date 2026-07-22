<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

abstract class AppException extends Exception
{
    /** @param array<string, mixed> $errors */
    public function __construct(
        string $message,
        private readonly int $status = 400,
        private readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->status;
    }

    /** @return array<string, mixed> */
    public function errors(): array
    {
        return $this->errors;
    }
}