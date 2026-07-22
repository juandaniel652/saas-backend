<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Auth\Services\AuthService;

final class AuthController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(Request $request, array $params): Response
    {
        $result = $this->authService->registerCompany($request->all());

        return ResponseHelper::success($result, 'Empresa registrada correctamente', 201);
    }

    public function login(Request $request, array $params): Response
    {
        $result = $this->authService->login($request->all());

        return ResponseHelper::success($result, 'Login exitoso');
    }

    public function me(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return ResponseHelper::success([
            'user_id' => $auth->userId,
            'company_id' => $auth->companyId,
            'roles' => $auth->roles,
            'permissions' => $auth->permissions,
        ]);
    }
}