<?php

declare(strict_types=1);

namespace App\Modules\Sales\Repositories;

use App\Core\Database\Connection;

final class SaleRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByCompany(int $companyId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM sales WHERE company_id = :company_id ORDER BY created_at DESC',
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM sales WHERE id = :id AND company_id = :company_id',
        );
        $stmt->execute(['id' => $id, 'company_id' => $companyId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Genera el proximo numero de comprobante para la empresa de forma atomica.
     * Debe llamarse dentro de una transaccion ya abierta.
     */
    public function nextInvoiceNumber(int $companyId): int
    {
        $pdo = $this->connection->pdo();

        $select = $pdo->prepare('SELECT next_number FROM invoice_counters WHERE company_id = :company_id FOR UPDATE');
        $select->execute(['company_id' => $companyId]);
        $row = $select->fetch();

        if ($row === false) {
            $pdo->prepare('INSERT INTO invoice_counters (company_id, next_number) VALUES (:company_id, 2)')
                ->execute(['company_id' => $companyId]);

            return 1;
        }

        $current = (int) $row['next_number'];

        $pdo->prepare('UPDATE invoice_counters SET next_number = next_number + 1 WHERE company_id = :company_id')
            ->execute(['company_id' => $companyId]);

        return $current;
    }

    public function create(
        int $companyId,
        int $branchId,
        ?int $clientId,
        ?int $appointmentId,
        int $invoiceNumber,
        float $total,
        string $status,
        string $paymentStatus,
        ?string $notes,
    ): int {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO sales
                (company_id, branch_id, client_id, appointment_id, invoice_number, total, status, payment_status, notes, created_at)
             VALUES
                (:company_id, :branch_id, :client_id, :appointment_id, :invoice_number, :total, :status, :payment_status, :notes, NOW())',
        );
        $stmt->execute([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'client_id' => $clientId,
            'appointment_id' => $appointmentId,
            'invoice_number' => $invoiceNumber,
            'total' => $total,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'notes' => $notes,
        ]);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    public function addItem(
        int $saleId,
        string $itemType,
        int $itemId,
        string $itemName,
        int $quantity,
        float $unitPrice,
        float $subtotal,
    ): void {
        $stmt = $this->connection->pdo()->prepare(
            'INSERT INTO sale_items (sale_id, item_type, item_id, item_name, quantity, unit_price, subtotal)
             VALUES (:sale_id, :item_type, :item_id, :item_name, :quantity, :unit_price, :subtotal)',
        );
        $stmt->execute([
            'sale_id' => $saleId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function itemsForSale(int $saleId): array
    {
        $stmt = $this->connection->pdo()->prepare('SELECT * FROM sale_items WHERE sale_id = :sale_id');
        $stmt->execute(['sale_id' => $saleId]);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->connection->pdo()->prepare('UPDATE sales SET status = :status WHERE id = :id');
        $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function updatePaymentStatus(int $id, string $paymentStatus): void
    {
        $stmt = $this->connection->pdo()->prepare('UPDATE sales SET payment_status = :payment_status WHERE id = :id');
        $stmt->execute(['id' => $id, 'payment_status' => $paymentStatus]);
    }
}