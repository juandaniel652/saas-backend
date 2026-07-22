<?php

declare(strict_types=1);

namespace App\Modules\Products\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Products\Services\ProductService;

final class ProductController
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->productService->listForCompany($auth->companyId));
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->productService->findOrFail((int) $params['id'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->productService->create($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Producto creado', 201);
    }

    public function adjustStock(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        $this->productService->adjustStock(
            (int) $params['id'],
            $auth->companyId,
            (string) $request->input('type'),
            (int) $request->input('quantity'),
            $request->input('reason') !== null ? (string) $request->input('reason') : null,
        );

        return ResponseHelper::success(null, 'Stock actualizado');
    }

    public function stockHistory(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->productService->stockHistory((int) $params['id'], $auth->companyId),
        );
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}