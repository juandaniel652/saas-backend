<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Appointments\Services\AppointmentService;

final class AppointmentController
{
    public function __construct(private readonly AppointmentService $appointmentService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        $employeeId = $request->input('employee_id') !== null ? (int) $request->input('employee_id') : null;
        $date = $request->input('date') !== null ? (string) $request->input('date') : null;

        return ResponseHelper::success(
            $this->appointmentService->listForCompany($auth->companyId, $employeeId, $date),
        );
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->appointmentService->findOrFail((int) $params['id'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->appointmentService->schedule($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Turno agendado', 201);
    }

    public function cancel(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $this->appointmentService->cancel((int) $params['id'], $auth->companyId, $auth);

        return ResponseHelper::success(null, 'Turno cancelado');
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}