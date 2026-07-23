<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Core\Auth\PasswordHasher;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validation\Validator;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\DTO\InviteUserDTO;
use App\Modules\Users\Repositories\UserRepository;

final class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly PasswordHasher $hasher,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        $users = $this->users->findByCompany($companyId);

        foreach ($users as &$user) {
            $user['roles'] = $this->users->rolesForUser((int) $user['id']);
        }

        return $users;
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $user = $this->users->findByIdAndCompany($id, $companyId);

        if ($user === null) {
            throw new NotFoundException('Usuario no encontrado');
        }

        $user['roles'] = $this->users->rolesForUser($id);

        return $user;
    }

    public function invite(int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:255',
        ])->validateOrFail();

        $dto = InviteUserDTO::fromArray($rawData);

        if ($this->users->findByEmailAndCompany($dto->email, $companyId) !== null) {
            throw new ValidationException(['email' => ['Ya existe un usuario con ese email en esta empresa']]);
        }

        $this->assertRolesBelongToCompany($dto->roleIds, $companyId);

        $userId = $this->users->create($companyId, $dto->name, $dto->email, $this->hasher->hash($dto->password));

        if ($dto->roleIds !== []) {
            $this->users->syncRoles($userId, $dto->roleIds);
        }

        return $userId;
    }

    /** @param int[] $roleIds */
    public function updateRoles(int $userId, int $companyId, array $roleIds): void
    {
        $this->findOrFail($userId, $companyId);
        $this->assertRolesBelongToCompany($roleIds, $companyId);

        $this->users->syncRoles($userId, $roleIds);
    }

    /** @param int[] $roleIds */
    private function assertRolesBelongToCompany(array $roleIds, int $companyId): void
    {
        foreach ($roleIds as $roleId) {
            if (!$this->roles->belongsToCompany($roleId, $companyId)) {
                throw new ValidationException(['role_ids' => ["El rol {$roleId} no pertenece a tu empresa"]]);
            }
        }
    }
}