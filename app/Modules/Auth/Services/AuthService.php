<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Auth\JwtManager;
use App\Core\Auth\PasswordHasher;
use App\Core\Database\Connection;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validation\Validator;
use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\DTO\RegisterCompanyDTO;
use App\Modules\Companies\Repositories\CompanyRepository;
use App\Modules\Permissions\Repositories\PermissionRepository;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;

final class AuthService
{
    /** Catalogo base de permisos que se siembra para toda empresa nueva. */
    private const BASE_PERMISSIONS = [
        'branches.view' => 'Ver sucursales',
        'branches.create' => 'Crear sucursales',
        'branches.manage' => 'Editar sucursales',
        'roles.manage' => 'Administrar roles y permisos',
        'users.view' => 'Ver usuarios',
        'users.manage' => 'Administrar usuarios',
        'settings.view' => 'Ver configuracion',
        'settings.manage' => 'Administrar configuracion',
        'clients.view' => 'Ver clientes',
        'clients.manage' => 'Administrar clientes',
        'employees.view' => 'Ver empleados',
        'employees.manage' => 'Administrar empleados',
        'services.view' => 'Ver servicios',
        'services.manage' => 'Administrar servicios',
        'schedule.view' => 'Ver horarios',
        'schedule.manage' => 'Administrar horarios',
        'appointments.view' => 'Ver turnos',
        'appointments.manage' => 'Administrar turnos',
        'appointments.cancel' => 'Cancelar turnos',
        'products.view' => 'Ver productos',
        'products.manage' => 'Administrar productos',
        'stock.manage' => 'Administrar stock',
        'sales.view' => 'Ver ventas',
        'sales.manage' => 'Registrar ventas',
        'sales.cancel' => 'Cancelar ventas',
        'payments.manage' => 'Registrar pagos',
        'reports.view' => 'Ver reportes',
        'audit.view' => 'Ver auditoria',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly CompanyRepository $companies,
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions,
        private readonly PasswordHasher $hasher,
        private readonly JwtManager $jwtManager,
    ) {
    }

    /** @return array{company_id: int, user_id: int} */
    public function registerCompany(array $rawData): array
    {
        Validator::make($rawData, [
            'company_name' => 'required|string|min:2|max:255',
            'owner_name' => 'required|string|min:2|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_password' => 'required|string|min:8|max:255',
        ])->validateOrFail();

        $dto = RegisterCompanyDTO::fromArray($rawData);
        $slug = $this->generateUniqueSlug($dto->companyName);

        $pdo = $this->connection->pdo();
        $pdo->beginTransaction();

        try {
            $companyId = $this->companies->create($dto->companyName, $slug);

            $passwordHash = $this->hasher->hash($dto->ownerPassword);
            $userId = $this->users->create($companyId, $dto->ownerName, $dto->ownerEmail, $passwordHash);

            $roleId = $this->roles->create($companyId, 'Owner', 'owner');

            foreach (self::BASE_PERMISSIONS as $slug2 => $description) {
                $permissionId = $this->permissions->create($slug2, $description);
                $this->roles->attachPermission($roleId, $permissionId);
            }

            $this->users->attachRole($userId, $roleId);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }

        return ['company_id' => $companyId, 'user_id' => $userId];
    }

    /** @return array{access_token: string, expires_in: int} */
    public function login(array $rawData): array
    {
        Validator::make($rawData, [
            'email' => 'required|email',
            'password' => 'required|string',
            'company_id' => 'required|integer',
        ])->validateOrFail();

        $dto = LoginDTO::fromArray($rawData);

        $user = $this->users->findByEmailAndCompany($dto->email, $dto->companyId);

        if ($user === null || !$this->hasher->verify($dto->password, $user['password_hash'])) {
            throw new UnauthorizedException('Credenciales invalidas');
        }

        $roles = $this->users->rolesForUser((int) $user['id']);
        $permissions = $this->users->permissionsForUser((int) $user['id']);

        $accessToken = $this->jwtManager->issueAccessToken([
            'sub' => (int) $user['id'],
            'company_id' => (int) $user['company_id'],
            'roles' => $roles,
            'permissions' => $permissions,
        ]);

        return [
            'access_token' => $accessToken,
            'expires_in' => $this->jwtManager->ttlSeconds(),
        ];
    }

    private function generateUniqueSlug(string $companyName): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $companyName), '-'));
        $slug = $base;
        $attempt = 1;

        while ($this->companies->slugExists($slug)) {
            $slug = $base . '-' . $attempt;
            $attempt++;
        }

        return $slug;
    }
}