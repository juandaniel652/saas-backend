<?php

declare(strict_types=1);

namespace App\Modules\Sales\Services;

use App\Core\Database\Connection;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validation\Validator;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Sales\DTO\CreateSaleDTO;
use App\Modules\Sales\Enums\PaymentStatus;
use App\Modules\Sales\Enums\SaleItemType;
use App\Modules\Sales\Enums\SaleStatus;
use App\Modules\Sales\Repositories\SaleRepository;
use App\Modules\Services\Repositories\ServiceCatalogRepository;
use App\Core\Events\EventDispatcher;
use App\Modules\Sales\Events\SaleCreated;
use App\Modules\Branches\Repositories\BranchRepository;
use Throwable;

final class SaleService
{
    public function __construct(
        private readonly SaleRepository $sales,
        private readonly ProductRepository $products,
        private readonly ServiceCatalogRepository $serviceCatalog,
        private readonly Connection $connection,
        private readonly EventDispatcher $events,
        private readonly BranchRepository $branches,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId): array
    {
        return $this->sales->findByCompany($companyId);
    }

    /** @return array{sale: array<string, mixed>, items: array<int, array<string, mixed>>} */
    public function findWithItems(int $id, int $companyId): array
    {
        $sale = $this->sales->findByIdAndCompany($id, $companyId);

        if ($sale === null) {
            throw new NotFoundException('Venta no encontrada');
        }

        return ['sale' => $sale, 'items' => $this->sales->itemsForSale($id)];
    }

    public function create(int $companyId, array $rawData, ?int $userId = null): array
    {
        Validator::make($rawData, [
            'branch_id' => 'required|integer',
        ])->validateOrFail();

        $dto = CreateSaleDTO::fromArray($rawData);

        if (!$this->branches->belongsToCompany($dto->branchId, $companyId)) {
            throw new ValidationException(['branch_id' => ['La sucursal indicada no pertenece a tu empresa']]);
        }

        if ($dto->items === []) {
            throw new ValidationException(['items' => ['La venta debe tener al menos un item']]);
        }

        $pdo = $this->connection->pdo();
        $pdo->beginTransaction();

        try {
            $resolvedItems = [];
            $total = 0.0;

            foreach ($dto->items as $item) {
                $resolved = $this->resolveItem($companyId, $item->itemType, $item->itemId, $item->quantity);
                $resolvedItems[] = $resolved;
                $total += $resolved['subtotal'];
            }

            $invoiceNumber = $this->sales->nextInvoiceNumber($companyId);

            $saleId = $this->sales->create(
                companyId: $companyId,
                branchId: $dto->branchId,
                clientId: $dto->clientId,
                appointmentId: $dto->appointmentId,
                invoiceNumber: $invoiceNumber,
                total: $total,
                status: SaleStatus::Completed->value,
                paymentStatus: PaymentStatus::Unpaid->value,
                notes: $dto->notes,
            );

            foreach ($resolvedItems as $resolved) {
                $this->sales->addItem(
                    $saleId,
                    $resolved['item_type'],
                    $resolved['item_id'],
                    $resolved['item_name'],
                    $resolved['quantity'],
                    $resolved['unit_price'],
                    $resolved['subtotal'],
                );

                if ($resolved['item_type'] === SaleItemType::Product->value) {
                    $this->products->adjustStock($resolved['item_id'], -$resolved['quantity']);
                    $this->products->recordMovement(
                        $companyId,
                        $resolved['item_id'],
                        'out',
                        $resolved['quantity'],
                        'Venta #' . $invoiceNumber,
                        'sale',
                        $saleId,
                    );
                }
            }

            $pdo->commit();

            $this->events->dispatch(new SaleCreated(
                saleId: $saleId,
                companyId: $companyId,
                userId: $userId,
                invoiceNumber: $invoiceNumber,
                total: $total,
            ));

            return ['id' => $saleId, 'invoice_number' => $invoiceNumber, 'total' => $total];
        } catch (Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }

    public function cancel(int $id, int $companyId): void
    {
        $data = $this->findWithItems($id, $companyId);
        $sale = $data['sale'];

        if ($sale['status'] === SaleStatus::Cancelled->value) {
            throw new ValidationException(['status' => ['La venta ya esta cancelada']]);
        }

        $pdo = $this->connection->pdo();
        $pdo->beginTransaction();

        try {
            foreach ($data['items'] as $item) {
                if ($item['item_type'] === SaleItemType::Product->value) {
                    $this->products->adjustStock((int) $item['item_id'], (int) $item['quantity']);
                    $this->products->recordMovement(
                        $companyId,
                        (int) $item['item_id'],
                        'in',
                        (int) $item['quantity'],
                        'Cancelacion venta #' . $sale['invoice_number'],
                        'sale_cancellation',
                        $id,
                    );
                }
            }

            $this->sales->updateStatus($id, SaleStatus::Cancelled->value);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }

    /** @return array{item_type: string, item_id: int, item_name: string, quantity: int, unit_price: float, subtotal: float} */
    private function resolveItem(int $companyId, string $itemType, int $itemId, int $quantity): array
    {
        if ($quantity <= 0) {
            throw new ValidationException(['items' => ['La cantidad de cada item debe ser mayor a cero']]);
        }

        if ($itemType === SaleItemType::Product->value) {
            $product = $this->products->findByIdForUpdate($itemId);

            if ($product === null || (int) $product['company_id'] !== $companyId) {
                throw new NotFoundException("Producto {$itemId} no encontrado");
            }

            if ((int) $product['stock_quantity'] < $quantity) {
                throw new ValidationException([
                    'items' => ["No hay stock suficiente de \"{$product['name']}\""],
                ]);
            }

            $unitPrice = (float) $product['price'];

            return [
                'item_type' => SaleItemType::Product->value,
                'item_id' => $itemId,
                'item_name' => $product['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($unitPrice * $quantity, 2),
            ];
        }

        if ($itemType === SaleItemType::Service->value) {
            $service = $this->serviceCatalog->findByIdAndCompany($itemId, $companyId);

            if ($service === null) {
                throw new NotFoundException("Servicio {$itemId} no encontrado");
            }

            $unitPrice = (float) $service['price'];

            return [
                'item_type' => SaleItemType::Service->value,
                'item_id' => $itemId,
                'item_name' => $service['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($unitPrice * $quantity, 2),
            ];
        }

        throw new ValidationException(['items' => ['item_type debe ser "product" o "service"']]);
    }
}