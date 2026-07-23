<?php

declare(strict_types=1);

namespace App\Modules\Roles\Services;

use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validation\Validator;
use App\Modules\Permissions\Repositories\PermissionRepository;
use App\Modules\Roles\DTO\CreateRoleDTO;
use App\Modules\Roles\Repositories\RoleRepository;

final class RoleService
{
    public function __construct(
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        $roles = $this->roles->findByCompany($companyId);

        foreach ($roles as &$role) {
            $role['permissions'] = array_column($this->roles->permissionsForRole((int) $role['id']), 'slug');
        }

        return $roles;
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $role = $this->roles->findByIdAndCompany($id, $companyId);

        if ($role === null) {
            throw new NotFoundException('Rol no encontrado');
        }

        $role['permissions'] = array_column($this->roles->permissionsForRole($id), 'slug');

        return $role;
    }

    /** @return array<int, array<string, mixed>> catalogo completo de permisos disponibles */
    public function listAllPermissions(): array
    {
        return $this->permissions->all();
    }

    public function create(int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
        ])->validateOrFail();

        $dto = CreateRoleDTO::fromArray($rawData);
        $slug = $this->generateUniqueSlug($companyId, $dto->name);

        $roleId = $this->roles->create($companyId, $dto->name, $slug);

        if ($dto->permissionSlugs !== []) {
            $this->attachPermissionsBySlug($roleId, $dto->permissionSlugs);
        }

        return $roleId;
    }

    /** @param string[] $permissionSlugs */
    public function updatePermissions(int $roleId, int $companyId, array $permissionSlugs): void
    {
        $this->findOrFail($roleId, $companyId);

        $permissions = $this->permissions->findBySlugs($permissionSlugs);

        if (count($permissions) !== count(array_unique($permissionSlugs))) {
            throw new ValidationException(['permission_slugs' => ['Alguno de los permisos indicados no existe']]);
        }

        $this->roles->syncPermissions($roleId, array_column($permissions, 'id'));
    }

    /** @param string[] $permissionSlugs */
    private function attachPermissionsBySlug(int $roleId, array $permissionSlugs): void
    {
        $permissions = $this->permissions->findBySlugs($permissionSlugs);

        foreach ($permissions as $permission) {
            $this->roles->attachPermission($roleId, (int) $permission['id']);
        }
    }

    private function generateUniqueSlug(int $companyId, string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $slug = $base;
        $attempt = 1;

        while ($this->roles->slugExists($companyId, $slug)) {
            $slug = $base . '-' . $attempt;
            $attempt++;
        }

        return $slug;
    }
}