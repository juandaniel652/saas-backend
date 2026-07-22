<?php

declare(strict_types=1);

namespace App\Modules\Services\Services;

use App\Core\Exceptions\NotFoundException;
use App\Core\Validation\Validator;
use App\Modules\Services\DTO\ServiceCatalogItemDTO;
use App\Modules\Services\Repositories\ServiceCatalogRepository;

final class ServiceCatalogService
{
    public function __construct(private readonly ServiceCatalogRepository $services)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        return $this->services->findByCompany($companyId);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $service = $this->services->findByIdAndCompany($id, $companyId);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        return $service;
    }

    public function create(int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
            'description' => 'max:500',
            'duration_minutes' => 'required|integer',
            'price' => 'required',
        ])->validateOrFail();

        $dto = ServiceCatalogItemDTO::fromArray($rawData);

        return $this->services->create($companyId, $dto->name, $dto->description, $dto->durationMinutes, $dto->price);
    }
}