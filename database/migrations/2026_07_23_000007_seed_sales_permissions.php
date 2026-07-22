<?php

declare(strict_types=1);

use PDO;

return [

    'up' => function (PDO $pdo): void {

        $permissions = [

            [
                'slug' => 'products.view',
                'description' => 'Ver productos',
            ],
            [
                'slug' => 'products.manage',
                'description' => 'Administrar productos',
            ],
            [
                'slug' => 'inventory.view',
                'description' => 'Ver inventario',
            ],
            [
                'slug' => 'inventory.manage',
                'description' => 'Administrar inventario',
            ],
            [
                'slug' => 'sales.view',
                'description' => 'Ver ventas',
            ],
            [
                'slug' => 'sales.manage',
                'description' => 'Administrar ventas',
            ],
            [
                'slug' => 'payments.manage',
                'description' => 'Administrar pagos',
            ],

        ];

        $insertPermission = $pdo->prepare(
            'INSERT INTO permissions (slug, description)
             VALUES (:slug, :description)'
        );

        $permissionExists = $pdo->prepare(
            'SELECT id
             FROM permissions
             WHERE slug = :slug'
        );

        foreach ($permissions as $permission) {

            $permissionExists->execute([
                'slug' => $permission['slug'],
            ]);

            if (!$permissionExists->fetch()) {

                $insertPermission->execute([
                    'slug' => $permission['slug'],
                    'description' => $permission['description'],
                ]);

            }

        }

        $ownerRoleId = (int) $pdo
            ->query("SELECT id FROM roles WHERE slug = 'owner'")
            ->fetchColumn();

        if ($ownerRoleId === 0) {
            return;
        }

        $permissionIds = $pdo->query(
            "SELECT id
             FROM permissions
             WHERE slug IN (
                'products.view',
                'products.manage',
                'inventory.view',
                'inventory.manage',
                'sales.view',
                'sales.manage',
                'payments.manage'
             )"
        )->fetchAll(PDO::FETCH_COLUMN);

        $relationExists = $pdo->prepare(
            'SELECT 1
             FROM role_permissions
             WHERE role_id = :role_id
             AND permission_id = :permission_id'
        );

        $insertRelation = $pdo->prepare(
            'INSERT INTO role_permissions (role_id, permission_id)
             VALUES (:role_id, :permission_id)'
        );

        foreach ($permissionIds as $permissionId) {

            $relationExists->execute([
                'role_id' => $ownerRoleId,
                'permission_id' => $permissionId,
            ]);

            if (!$relationExists->fetch()) {

                $insertRelation->execute([
                    'role_id' => $ownerRoleId,
                    'permission_id' => $permissionId,
                ]);

            }

        }

    },

    'down' => function (PDO $pdo): void {

        $slugs = [
            'products.view',
            'products.manage',
            'inventory.view',
            'inventory.manage',
            'sales.view',
            'sales.manage',
            'payments.manage',
        ];

        $placeholders = implode(',', array_fill(0, count($slugs), '?'));

        $pdo->prepare(
            "DELETE FROM role_permissions
             WHERE permission_id IN (
                SELECT id
                FROM permissions
                WHERE slug IN ($placeholders)
             )"
        )->execute($slugs);

        $pdo->prepare(
            "DELETE FROM permissions
             WHERE slug IN ($placeholders)"
        )->execute($slugs);

    },

];