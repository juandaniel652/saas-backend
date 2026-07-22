<?php

declare(strict_types=1);

namespace App\Modules\Clients\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Clients\Services\ClientService;

final class ClientController
{
    public function __construct(private readonly ClientService $clientService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success($this->clientService->listForCompany($auth->companyId));
    }

    public function show(Request $request, array $params): Response
    {
        $auth = $this->auth($request);

        return ResponseHelper::success(
            $this->clientService->findOrFail((int) $params['id'], $auth->companyId),
        );
    }

    public function store(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $id = $this->clientService->create($auth->companyId, $request->all());

        return ResponseHelper::success(['id' => $id], 'Cliente creado', 201);
    }

    public function update(Request $request, array $params): Response
    {
        $auth = $this->auth($request);
        $this->clientService->update((int) $params['id'], $auth->companyId, $request->all());

        return ResponseHelper::success(null, 'Cliente actualizado');
    }

    private function auth(Request $request): AuthenticatedUser
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return $auth;
    }
}