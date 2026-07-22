<?php

declare(strict_types=1);

namespace App\Modules\Products\Repositories;

use App\Core\Database\Connection;

final class ProductRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM products WHERE company_id = :company_id ORDER BY name',
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM products WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** Bloquea la fila para actualizar stock de forma segura dentro de una transaccion. */
    public function findByIdForUpdate(int $id): ?array
    {
        $stmt = $this->connection->pdo()->prepare('SELECT * FROM products WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(int $companyId, string $name, ?string $sku, float $price, int $initialStock): int
    {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO products (company_id, name, sku, price, stock_quantity, created_at)
             VALUES (:company_id, :name, :sku, :price, :stock_quantity, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'stock_quantity' => $initialStock,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    public function adjustStock(int $productId, int $delta): void
    {
        $stmt = $this->connection->pdo()->prepare(
            'UPDATE products SET stock_quantity = stock_quantity + :delta WHERE id = :id',
        );
        $stmt->execute(['delta' => $delta, 'id' => $productId]);
    }

    public function recordMovement(
        int $companyId,
        int $productId,
        string $type,
        int $quantity,
        ?string $reason,
        ?string $referenceType,
        ?int $referenceId,
    ): void {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO stock_movements
                (company_id, product_id, type, quantity, reason, reference_type, reference_id, created_at)
             VALUES
                (:company_id, :product_id, :type, :quantity, :reason, :reference_type, :reference_id, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function movementsForProduct(int $productId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM stock_movements WHERE product_id = :product_id ORDER BY created_at DESC',
        );
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll();
    }
}