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

    public function update(int $id, int $companyId, array $rawData): void
    {
        $branch = $this->branches->findByIdAndCompany($id, $companyId);
    
        if ($branch === null) {
            throw new \App\Core\Exceptions\NotFoundException('Sucursal no encontrada');
        }
    
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
            'address' => 'max:255',
        ])->validateOrFail();
    
        $dto = CreateBranchDTO::fromArray($rawData);
        $this->branches->update($id, $dto->name, $dto->address);
    }
}