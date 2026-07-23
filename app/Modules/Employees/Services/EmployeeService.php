<?php

declare(strict_types=1);

namespace App\Modules\Employees\Services;

use App\Core\Exceptions\NotFoundException;
use App\Core\Validation\Validator;
use App\Modules\Employees\DTO\EmployeeDTO;
use App\Modules\Employees\Repositories\EmployeeRepository;
use App\Core\Exceptions\ValidationException;
use App\Modules\Branches\Repositories\BranchRepository;

final class EmployeeService
{
    public function __construct(private readonly EmployeeRepository $employees, private readonly BranchRepository $branches)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        return $this->employees->findByCompany($companyId);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $employee = $this->employees->findByIdAndCompany($id, $companyId);

        if ($employee === null) {
            throw new NotFoundException('Empleado no encontrado');
        }

        return $employee;
    }

    public function create(int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'branch_id' => 'required|integer',
            'name' => 'required|string|min:2|max:255',
            'email' => 'email|max:255',
            'phone' => 'max:50',
        ])->validateOrFail();
    
        $dto = EmployeeDTO::fromArray($rawData);
    
        if (!$this->branches->belongsToCompany($dto->branchId, $companyId)) {
            throw new ValidationException(['branch_id' => ['La sucursal indicada no pertenece a tu empresa']]);
        }
    
        $employeeId = $this->employees->create($companyId, $dto->branchId, $dto->name, $dto->email, $dto->phone);
    
        if ($dto->serviceIds !== []) {
            $this->employees->syncServices($employeeId, $dto->serviceIds);
        }
    
        return $employeeId;
    }

    /** @param int[] $serviceIds */
    public function assignServices(int $employeeId, int $companyId, array $serviceIds): void
    {
        $this->findOrFail($employeeId, $companyId);
        $this->employees->syncServices($employeeId, $serviceIds);
    }

    /** @return array<int, array<string, mixed>> */
    public function servicesFor(int $employeeId, int $companyId): array
    {
        $this->findOrFail($employeeId, $companyId);

        return $this->employees->servicesForEmployee($employeeId);
    }
}