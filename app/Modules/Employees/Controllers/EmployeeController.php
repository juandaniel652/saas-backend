<?php

declare(strict_types=1);

namespace App\Modules\Employees\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Employees\Services\EmployeeService;

final class EmployeeController
{
    public function __construct(private readonly EmployeeService $employeeService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->employeeService->listForCompany($auth->companyId));
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->employeeService->findOrFail((int) $params['id'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->employeeService->create($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Empleado creado', 201);
    }

    public function services(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->employeeService->servicesFor((int) $params['id'], $auth->companyId),
        );
    }

    public function assignServices(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $serviceIds = array_map('intval', $request->input('service_ids', []));

        $this->employeeService->assignServices((int) $params['id'], $auth->companyId, $serviceIds);

        return ResponseHelper::success(null, 'Servicios asignados');
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}