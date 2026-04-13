<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

final class Invoice extends Model
{
    public function all(): array
    {
        $sql = 'SELECT i.*, c.company_name AS client_name, u.full_name AS user_name
                FROM invoices i
                INNER JOIN clients c ON c.id = i.client_id
                INNER JOIN users u ON u.id = i.created_by
                WHERE i.deleted_at IS NULL
                ORDER BY i.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT i.*, c.company_name AS client_name, c.contact_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address, c.city AS client_city, q.quote_number, u.full_name AS user_name
                FROM invoices i
                INNER JOIN clients c ON c.id = i.client_id
                LEFT JOIN quotes q ON q.id = i.quote_id
                INNER JOIN users u ON u.id = i.created_by
                WHERE i.id = :id AND i.deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $invoice = $statement->fetch();
        return $invoice ?: null;
    }

    public function items(int $invoiceId): array
    {
        $sql = 'SELECT ii.*, p.sku, p.name AS product_name, s.code AS service_code, s.name AS service_name
                FROM invoice_items ii
                LEFT JOIN products p ON p.id = ii.product_id
                LEFT JOIN services s ON s.id = ii.service_id
                WHERE ii.invoice_id = :invoice_id
                ORDER BY ii.id ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['invoice_id' => $invoiceId]);
        return $statement->fetchAll();
    }

    public function payableOptions(): array
    {
        $sql = "SELECT i.id, i.invoice_number, i.balance_due, c.company_name AS client_name
                FROM invoices i
                INNER JOIN clients c ON c.id = i.client_id
                WHERE i.deleted_at IS NULL
                  AND i.status IN ('validated', 'partial_paid')
                  AND i.balance_due > 0
                ORDER BY i.id DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function createWithItems(array $header, array $items): int
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare('INSERT INTO invoices (quote_id, client_id, invoice_number, invoice_date, due_date, status, subtotal, discount_amount, tax_rate, tax_amount, grand_total, amount_paid, balance_due, notes, validated_at, cancelled_at, created_by, validated_by, deleted_at, created_at, updated_at)
                VALUES (:quote_id, :client_id, :invoice_number, :invoice_date, :due_date, :status, :subtotal, :discount_amount, :tax_rate, :tax_amount, :grand_total, :amount_paid, :balance_due, :notes, :validated_at, :cancelled_at, :created_by, :validated_by, NULL, NOW(), NOW())');
            $statement->execute($header);
            $invoiceId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare('INSERT INTO invoice_items (invoice_id, item_type, product_id, service_id, description, quantity, unit_price, discount_amount, tax_amount, line_total, created_at)
                VALUES (:invoice_id, :item_type, :product_id, :service_id, :description, :quantity, :unit_price, :discount_amount, :tax_amount, :line_total, NOW())');
            foreach ($items as $item) {
                $itemStatement->execute([
                    'invoice_id' => $invoiceId,
                    'item_type' => $item['item_type'],
                    'product_id' => $item['product_id'],
                    'service_id' => $item['service_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'tax_amount' => $item['tax_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }

            $this->db->commit();
            return $invoiceId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function createFromQuote(int $quoteId, int $userId, string $invoiceNumber, string $invoiceDate, ?string $dueDate = null): int
    {
        $quoteModel = new Quote();
        $quote = $quoteModel->find($quoteId);
        $items = $quoteModel->items($quoteId);

        if (!$quote || $items === []) {
            throw new \RuntimeException('Devis introuvable ou vide.');
        }

        $header = [
            'quote_id' => $quoteId,
            'client_id' => (int) $quote['client_id'],
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'status' => 'draft',
            'subtotal' => (float) $quote['subtotal'],
            'discount_amount' => (float) $quote['discount_amount'],
            'tax_rate' => (float) ($quote['tax_rate'] ?? 0),
            'tax_amount' => (float) $quote['tax_amount'],
            'grand_total' => (float) $quote['grand_total'],
            'amount_paid' => 0,
            'balance_due' => (float) $quote['grand_total'],
            'notes' => (string) $quote['notes'],
            'validated_at' => null,
            'cancelled_at' => null,
            'created_by' => $userId,
            'validated_by' => null,
        ];

        $invoiceItems = array_map(static function (array $item): array {
            return [
                'item_type' => $item['item_type'],
                'product_id' => $item['product_id'] ? (int) $item['product_id'] : null,
                'service_id' => $item['service_id'] ? (int) $item['service_id'] : null,
                'description' => (string) $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'discount_amount' => (float) $item['discount_amount'],
                'tax_amount' => (float) $item['tax_amount'],
                'line_total' => (float) $item['line_total'],
            ];
        }, $items);

        return $this->createWithItems($header, $invoiceItems);
    }

    public function validateInvoice(int $id, int $userId): void
    {
        $this->db->beginTransaction();

        try {
            $invoice = $this->find($id);
            if (!$invoice || $invoice['status'] === 'validated' || $invoice['status'] === 'partial_paid' || $invoice['status'] === 'paid' || $invoice['status'] === 'cancelled') {
                $this->db->rollBack();
                return;
            }

            $items = $this->items($id);
            $productModel = new Product();
            $movementModel = new StockMovement();

            foreach ($items as $item) {
                if ($item['item_type'] !== 'product' || !$item['product_id']) {
                    continue;
                }

                $product = $productModel->find((int) $item['product_id']);
                if (!$product) {
                    continue;
                }

                $before = (float) $product['current_stock'];
                $quantity = (float) $item['quantity'];
                $after = $before - $quantity;

                if ($after < 0) {
                    throw new \RuntimeException('Stock insuffisant pour ' . $product['name']);
                }

                $productModel->adjustStock((int) $product['id'], $after);
                $movementModel->create([
                    'product_id' => (int) $product['id'],
                    'movement_type' => 'invoice_validation',
                    'quantity' => -$quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reference_type' => 'invoice',
                    'reference_id' => $id,
                    'note' => 'Validation facture #' . $id,
                    'movement_date' => date('Y-m-d H:i:s'),
                    'created_by' => $userId,
                ]);
            }

            $status = ((float) $invoice['amount_paid']) >= (float) $invoice['grand_total'] && (float) $invoice['grand_total'] > 0 ? 'paid' : (((float) $invoice['amount_paid']) > 0 ? 'partial_paid' : 'validated');
            $statement = $this->db->prepare('UPDATE invoices SET status = :status, validated_at = NOW(), validated_by = :validated_by, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            $statement->execute([
                'status' => $status,
                'validated_by' => $userId,
                'id' => $id,
            ]);

            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function cancelInvoice(int $id, int $userId): void
    {
        $this->db->beginTransaction();

        try {
            $invoice = $this->find($id);
            if (!$invoice || $invoice['status'] === 'cancelled') {
                $this->db->rollBack();
                return;
            }

            if (in_array($invoice['status'], ['validated', 'partial_paid', 'paid'], true)) {
                $items = $this->items($id);
                $productModel = new Product();
                $movementModel = new StockMovement();

                foreach ($items as $item) {
                    if ($item['item_type'] !== 'product' || !$item['product_id']) {
                        continue;
                    }

                    $product = $productModel->find((int) $item['product_id']);
                    if (!$product) {
                        continue;
                    }

                    $before = (float) $product['current_stock'];
                    $quantity = (float) $item['quantity'];
                    $after = $before + $quantity;

                    $productModel->adjustStock((int) $product['id'], $after);
                    $movementModel->create([
                        'product_id' => (int) $product['id'],
                        'movement_type' => 'invoice_cancellation',
                        'quantity' => $quantity,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'reference_type' => 'invoice',
                        'reference_id' => $id,
                        'note' => 'Annulation facture #' . $id,
                        'movement_date' => date('Y-m-d H:i:s'),
                        'created_by' => $userId,
                    ]);
                }
            }

            $statement = $this->db->prepare("UPDATE invoices SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL");
            $statement->execute(['id' => $id]);
            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function refreshPaymentStatus(int $id): void
    {
        $statement = $this->db->prepare('SELECT grand_total, COALESCE(SUM(amount), 0) AS paid FROM invoices i LEFT JOIN payments p ON p.invoice_id = i.id AND p.deleted_at IS NULL WHERE i.id = :id GROUP BY i.id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if (!$row) {
            return;
        }

        $grandTotal = (float) $row['grand_total'];
        $paid = (float) $row['paid'];
        $balance = max($grandTotal - $paid, 0);

        $status = 'draft';
        $invoice = $this->find($id);
        if (!$invoice) {
            return;
        }

        if ($invoice['status'] === 'cancelled') {
            $status = 'cancelled';
        } elseif ($invoice['validated_at'] !== null) {
            if ($paid >= $grandTotal && $grandTotal > 0) {
                $status = 'paid';
            } elseif ($paid > 0) {
                $status = 'partial_paid';
            } else {
                $status = 'validated';
            }
        }

        $update = $this->db->prepare('UPDATE invoices SET amount_paid = :amount_paid, balance_due = :balance_due, status = :status, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'amount_paid' => $paid,
            'balance_due' => $balance,
            'status' => $status,
            'id' => $id,
        ]);
    }
}
