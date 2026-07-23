<?php

declare(strict_types=1);

namespace App\Modules\Settings\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Settings\Services\SettingsService;

final class SettingsController
{
    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function show(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        return ResponseHelper::success($this->settingsService->allForCompany($auth->companyId));
    }

    public function update(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        $this->settingsService->update($auth->companyId, $request->all());

        return ResponseHelper::success($this->settingsService->allForCompany($auth->companyId), 'Configuracion actualizada');
    }
}