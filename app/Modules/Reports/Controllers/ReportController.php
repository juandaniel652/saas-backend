<?php

declare(strict_types=1);

namespace App\Modules\Reports\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Reports\Services\ReportService;

final class ReportController
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function salesSummary(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->reportService->salesSummary($auth->companyId, $this->dateOrNull($request, 'from'), $this->dateOrNull($request, 'to')),
        );
    }

    public function appointmentsSummary(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->reportService->appointmentsSummary($auth->companyId, $this->dateOrNull($request, 'from'), $this->dateOrNull($request, 'to')),
        );
    }

    public function topServices(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $limit = $request->input('limit') !== null ? (int) $request->input('limit') : 5;

        return ResponseHelper::success(
            $this->reportService->topServices($auth->companyId, $this->dateOrNull($request, 'from'), $this->dateOrNull($request, 'to'), $limit),
        );
    }

    private function dateOrNull(Request $request, string $key): ?string
    {
        $value = $request->input($key);

        return $value !== null ? (string) $value : null;
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}