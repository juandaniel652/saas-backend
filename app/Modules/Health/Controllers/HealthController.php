<?php

declare(strict_types=1);

namespace App\Modules\Health\Controllers;

use App\Core\Database\Connection;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;

final class HealthController
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function ping(Request $request, array $params): Response
    {
        $this->connection->pdo(); // valida que la conexion a la base de datos funciona

        return ResponseHelper::success([
            'status' => 'ok',
            'timestamp' => date(DATE_ATOM),
        ], 'El sistema esta funcionando');
    }
}