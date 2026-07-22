<?php

declare(strict_types=1);

namespace App\Modules\Audit\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Audit\Repositories\AuditRepository;

final class AuditController
{
    public function __construct(private readonly AuditRepository $audit)
    {
    }

    public function index(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        $limit = $request->input('limit') !== null ? (int) $request->input('limit') : 100;

        return ResponseHelper::success($this->audit->findByCompany($auth->companyId, $limit));
    }
}