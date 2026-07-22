<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use App\Core\Exceptions\NotFoundException;
use App\Core\Validation\Validator;
use App\Modules\Clients\DTO\ClientDTO;
use App\Modules\Clients\Repositories\ClientRepository;

final class ClientService
{
    public function __construct(private readonly ClientRepository $clients)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        return $this->clients->findByCompany($companyId);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $client = $this->clients->findByIdAndCompany($id, $companyId);

        if ($client === null) {
            throw new NotFoundException('Cliente no encontrado');
        }

        return $client;
    }

    public function create(int $companyId, array $rawData): int
    {
        $this->validate($rawData);
        $dto = ClientDTO::fromArray($rawData);

        return $this->clients->create($companyId, $dto->name, $dto->email, $dto->phone, $dto->notes);
    }

    public function update(int $id, int $companyId, array $rawData): void
    {
        $this->findOrFail($id, $companyId);
        $this->validate($rawData);
        $dto = ClientDTO::fromArray($rawData);

        $this->clients->update($id, $dto->name, $dto->email, $dto->phone, $dto->notes);
    }

    private function validate(array $rawData): void
    {
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
            'email' => 'email|max:255',
            'phone' => 'max:50',
            'notes' => 'max:500',
        ])->validateOrFail();
    }
}