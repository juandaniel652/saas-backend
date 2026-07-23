<?php

declare(strict_types=1);

namespace App\Modules\Roles\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Roles\Services\RoleService;

final class RoleController
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->roleService->listForCompany($auth->companyId));
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->roleService->findOrFail((int) $params['id'], $auth->companyId));
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->roleService->create($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Rol creado', 201);
    }

    public function updatePermissions(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $slugs = array_map('strval', $request->input('permission_slugs', []));

        $this->roleService->updatePermissions((int) $params['id'], $auth->companyId, $slugs);

        return ResponseHelper::success(null, 'Permisos actualizados');
    }

    public function permissionsCatalog(Request $request, array $params): Response
    {
        return ResponseHelper::success($this->roleService->listAllPermissions());
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}