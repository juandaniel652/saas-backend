<?php

declare(strict_types=1);

namespace App\Core\Http;

final class ResponseHelper
{
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        ?array $pagination = null,
    ): Response {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ];

        if ($pagination !== null) {
            $payload['pagination'] = $pagination;
        }

        return new Response($payload, $status);
    }

    public static function error(string $message, array $errors = [], int $status = 400): Response
    {
        return new Response([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }
}