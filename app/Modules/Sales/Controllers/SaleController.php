<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Sales\Services\SaleService;

final class SaleController
{
    public function __construct(private readonly SaleService $saleService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->saleService->listForCompany($auth->companyId));
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->saleService->findWithItems((int) $params['id'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $result = $this->saleService->create($auth->companyId, $request->all());

        return ResponseHelper::success($result, 'Venta registrada', 201);
    }

    public function cancel(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $this->saleService->cancel((int) $params['id'], $auth->companyId);

        return ResponseHelper::success(null, 'Venta cancelada');
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}