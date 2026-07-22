<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Core\Database\Connection;
use App\Core\Exceptions\ValidationException;
use App\Core\Validation\Validator;
use App\Modules\Payments\DTO\RegisterPaymentDTO;
use App\Modules\Payments\Repositories\PaymentRepository;
use App\Modules\Sales\Enums\PaymentStatus;
use App\Modules\Sales\Enums\SaleStatus;
use App\Modules\Sales\Repositories\SaleRepository;
use DateTimeImmutable;
use Throwable;

final class PaymentService
{
    private const VALID_METHODS = ['cash', 'card', 'transfer', 'mercado_pago', 'stripe'];

    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly SaleRepository $sales,
        private readonly Connection $connection,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForSale(int $saleId, int $companyId): array
    {
        $this->getSaleOrFail($saleId, $companyId);

        return $this->payments->findBySale($saleId);
    }

    public function register(int $saleId, int $companyId, array $rawData): int
    {
        Validator::make($rawData, [
            'amount' => 'required',
            'method' => 'required|string',
        ])->validateOrFail();

        if (!in_array($rawData['method'], self::VALID_METHODS, true)) {
            throw new ValidationException([
                'method' => ['El metodo de pago debe ser uno de: ' . implode(', ', self::VALID_METHODS)],
            ]);
        }

        $dto = RegisterPaymentDTO::fromArray($rawData);

        if ($dto->amount <= 0) {
            throw new ValidationException(['amount' => ['El monto debe ser mayor a cero']]);
        }

        $sale = $this->getSaleOrFail($saleId, $companyId);

        if ($sale['status'] === SaleStatus::Cancelled->value) {
            throw new ValidationException(['sale_id' => ['No se pueden registrar pagos sobre una venta cancelada']]);
        }

        $pdo = $this->connection->pdo();
        $pdo->beginTransaction();

        try {
            $alreadyPaid = $this->payments->totalPaidForSale($saleId);
            $pending = round((float) $sale['total'] - $alreadyPaid, 2);

            if ($dto->amount > $pending) {
                throw new ValidationException([
                    'amount' => ["El monto excede el saldo pendiente ({$pending})"],
                ]);
            }

            $paidAt = $dto->paidAt ?? (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $paymentId = $this->payments->create($saleId, $dto->amount, $dto->method, $paidAt);

            $newTotalPaid = round($alreadyPaid + $dto->amount, 2);
            $newStatus = $newTotalPaid >= (float) $sale['total']
                ? PaymentStatus::Paid
                : PaymentStatus::Partial;

            $this->sales->updatePaymentStatus($saleId, $newStatus->value);

            $pdo->commit();

            return $paymentId;
        } catch (Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }

    /** @return array<string, mixed> */
    private function getSaleOrFail(int $saleId, int $companyId): array
    {
        $sale = $this->sales->findByIdAndCompany($saleId, $companyId);

        if ($sale === null) {
            throw new \App\Core\Exceptions\NotFoundException('Venta no encontrada');
        }

        return $sale;
    }
}