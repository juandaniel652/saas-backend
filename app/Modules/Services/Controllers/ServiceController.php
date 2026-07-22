<?php

declare(strict_types=1);

namespace App\Modules\Services\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Services\Services\ServiceCatalogService;

final class ServiceController
{
    public function __construct(private readonly ServiceCatalogService $serviceCatalog)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->serviceCatalog->listForCompany($auth->companyId));
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->serviceCatalog->create($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Servicio creado', 201);
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}