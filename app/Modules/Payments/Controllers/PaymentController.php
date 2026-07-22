<?php

declare(strict_types=1);

namespace App\Modules\Payments\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Payments\Services\PaymentService;

final class PaymentController
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->paymentService->listForSale((int) $params['saleId'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        $id = $this->paymentService->register((int) $params['saleId'], $auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Pago registrado', 201);
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}