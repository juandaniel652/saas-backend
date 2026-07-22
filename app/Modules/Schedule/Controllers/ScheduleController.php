<?php

declare(strict_types=1);

namespace App\Modules\Schedule\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Schedule\Services\ScheduleService;

final class ScheduleController
{
    public function __construct(private readonly ScheduleService $scheduleService)
    {
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->scheduleService->forEmployee((int) $params['id'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        $this->scheduleService->setWeeklySchedule((int) $params['id'], $auth->companyId, $request->all());

        return ResponseHelper::success(null, 'Horario actualizado');
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}