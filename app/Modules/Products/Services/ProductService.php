<?php

declare(strict_types=1);

namespace App\Modules\Products\Services;

use App\Core\Database\Connection;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validation\Validator;
use App\Modules\Products\DTO\ProductDTO;
use App\Modules\Products\Repositories\ProductRepository;
use Throwable;

final class ProductService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly Connection $connection,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        return $this->products->findByCompany($companyId);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $product = $this->products->findByIdAndCompany($id, $companyId);

        if ($product === null) {
            throw new NotFoundException('Producto no encontrado');
        }

        return $product;
    }

    public function create(int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'name' => 'required|string|min:2|max:255',
            'sku' => 'max:100',
            'price' => 'required',
            'initial_stock' => 'integer',
        ])->validateOrFail();

        $dto = ProductDTO::fromArray($rawData);

        return $this->products->create($companyId, $dto->name, $dto->sku, $dto->price, $dto->initialStock);
    }

    /**
     * Ajuste manual de stock (ej: compra a proveedor, merma, correccion de inventario).
     * type: "in" suma, "out" resta.
     */
    public function adjustStock(int $productId, int $companyId, string $type, int $quantity, ?string $reason): void
    {
        if (!in_array($type, ['in', 'out'], true)) {
            throw new ValidationException(['type' => ['El tipo debe ser "in" o "out"']]);
        }

        if ($quantity <= 0) {
            throw new ValidationException(['quantity' => ['La cantidad debe ser mayor a cero']]);
        }

        $pdo = $this->connection->pdo();
        $pdo->beginTransaction();

        try {
            $product = $this->products->findByIdForUpdate($productId);

            if ($product === null || (int) $product['company_id'] !== $companyId) {
                throw new NotFoundException('Producto no encontrado');
            }

            $delta = $type === 'in' ? $quantity : -$quantity;

            if ($type === 'out' && (int) $product['stock_quantity'] + $delta < 0) {
                throw new ValidationException(['quantity' => ['No hay stock suficiente para este ajuste']]);
            }

            $this->products->adjustStock($productId, $delta);
            $this->products->recordMovement($companyId, $productId, $type, $quantity, $reason, 'manual_adjustment', null);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function stockHistory(int $productId, int $companyId): array
    {
        $this->findOrFail($productId, $companyId);

        return $this->products->movementsForProduct($productId);
    }
}