<?php

declare(strict_types=1);

namespace App\Modules\Branches\Services;

use App\Core\Validation\Validator;
use App\Modules\Branches\DTO\CreateBranchDTO;
use App\Modules\Branches\Repositories\BranchRepository;

final class BranchService
{
    public function __construct(private readonly BranchRepository $branches)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        return $this->branches->findByCompany($companyId);
    }

    public function create(int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
            'address' => 'max:255',
        ])->validateOrFail();

        $dto = CreateBranchDTO::fromArray($rawData);

        return $this->branches->create($companyId, $dto->name, $dto->address);
    }
}