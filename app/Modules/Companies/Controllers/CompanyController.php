<?php

declare(strict_types=1);

namespace App\Modules\Companies\Controllers;

use App\Core\Auth\AuthenticatedUser;
use App\Core\Exceptions\NotFoundException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\ResponseHelper;
use App\Modules\Companies\Repositories\CompanyRepository;

final class CompanyController
{
    public function __construct(private readonly CompanyRepository $companies)
    {
    }

    public function me(Request $request, array $params): Response
    {
        /** @var AuthenticatedUser $auth */
        $auth = $request->attribute('auth');

        $company = $this->companies->findById($auth->companyId);

        if ($company === null) {
            throw new NotFoundException('Empresa no encontrada');
        }

        return ResponseHelper::success($company);
    }
}