<?php

declare(strict_types=1);

namespace App\Modules\Branches\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Branches\Services\BranchService;

final class BranchController
{
    public function __construct(private readonly BranchService $branchService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return ResponseHelper::success($this->branchService->listForCompany($auth->companyId));
    }

    public function store(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        $id = $this->branchService->create($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Sucursal creada', 201);
    }

    public function update(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');
    
        $this->branchService->update((int) $params['id'], $auth->companyId, $request->all());
    
        return ResponseHelper::success(null, 'Sucursal actualizada');
    }
}