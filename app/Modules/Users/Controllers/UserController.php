<?php

declare(strict_types=1);

namespace App\Modules\Users\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Users\Services\UserService;

final class UserController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->userService->listForCompany($auth->companyId));
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->userService->findOrFail((int) $params['id'], $auth->companyId));
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->userService->invite($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Usuario invitado correctamente', 201);
    }

    public function updateRoles(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $roleIds = array_map('intval', $request->input('role_ids', []));

        $this->userService->updateRoles((int) $params['id'], $auth->companyId, $roleIds);

        return ResponseHelper::success(null, 'Roles actualizados');
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}