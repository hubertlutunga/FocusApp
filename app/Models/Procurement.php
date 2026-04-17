<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;
use Throwable;

final class Procurement extends Model
{
    public function all(): array
    {
        $sql = 'SELECT pr.*, s.company_name AS supplier_name, u.full_name AS user_name
                FROM procurements pr
                INNER JOIN suppliers s ON s.id = pr.supplier_id
                INNER JOIN users u ON u.id = pr.user_id
                WHERE pr.deleted_at IS NULL
                ORDER BY pr.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT pr.*, s.company_name AS supplier_name, u.full_name AS user_name
                FROM procurements pr
                INNER JOIN suppliers s ON s.id = pr.supplier_id
                INNER JOIN users u ON u.id = pr.user_id
                WHERE pr.id = :id AND pr.deleted_at IS NULL
                LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $procurement = $statement->fetch();
        return $procurement ?: null;
    }

    public function items(int $procurementId): array
    {
        $sql = 'SELECT pi.*, p.sku, p.name AS product_name, u.symbol AS unit_symbol
                FROM procurement_items pi
                INNER JOIN products p ON p.id = pi.product_id
                INNER JOIN units u ON u.id = p.unit_id
                WHERE pi.procurement_id = :procurement_id
                ORDER BY pi.id ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute(['procurement_id' => $procurementId]);
        return $statement->fetchAll();
    }

    public function createWithItems(array $header, array $items): int
    {
        $this->db->beginTransaction();

        try {
            $creditSchemaEnabled = $this->supportsCreditTracking();

            if (!$creditSchemaEnabled && $header['payment_method'] === 'credit') {
                throw new RuntimeException('La base de donnees doit etre migree avant d utiliser les approvisionnements a credit.');
            }

            if ($creditSchemaEnabled) {
                $statement = $this->db->prepare('INSERT INTO procurements (supplier_id, user_id, procurement_number, procurement_date, expected_date, received_date, status, payment_method, payment_status, amount_paid, balance_due, settled_at, subtotal, discount_amount, tax_amount, grand_total, notes, deleted_at, created_at, updated_at)
                    VALUES (:supplier_id, :user_id, :procurement_number, :procurement_date, :expected_date, :received_date, :status, :payment_method, :payment_status, :amount_paid, :balance_due, :settled_at, :subtotal, :discount_amount, :tax_amount, :grand_total, :notes, NULL, NOW(), NOW())');
                $statement->execute([
                    'supplier_id' => $header['supplier_id'],
                    'user_id' => $header['user_id'],
                    'procurement_number' => $header['procurement_number'],
                    'procurement_date' => $header['procurement_date'],
                    'expected_date' => $header['expected_date'],
                    'received_date' => $header['received_date'],
                    'status' => $header['status'],
                    'payment_method' => $header['payment_method'],
                    'payment_status' => $header['payment_method'] === 'credit' ? 'unpaid' : 'paid',
                    'amount_paid' => $header['payment_method'] === 'credit' ? 0 : $header['grand_total'],
                    'balance_due' => $header['payment_method'] === 'credit' ? $header['grand_total'] : 0,
                    'settled_at' => $header['payment_method'] === 'credit' ? null : date('Y-m-d H:i:s'),
                    'subtotal' => $header['subtotal'],
                    'discount_amount' => $header['discount_amount'],
                    'tax_amount' => $header['tax_amount'],
                    'grand_total' => $header['grand_total'],
                    'notes' => $header['notes'],
                ]);
            } else {
                $statement = $this->db->prepare('INSERT INTO procurements (supplier_id, user_id, procurement_number, procurement_date, expected_date, received_date, status, subtotal, discount_amount, tax_amount, grand_total, notes, deleted_at, created_at, updated_at)
                    VALUES (:supplier_id, :user_id, :procurement_number, :procurement_date, :expected_date, :received_date, :status, :subtotal, :discount_amount, :tax_amount, :grand_total, :notes, NULL, NOW(), NOW())');
                $statement->execute([
                    'supplier_id' => $header['supplier_id'],
                    'user_id' => $header['user_id'],
                    'procurement_number' => $header['procurement_number'],
                    'procurement_date' => $header['procurement_date'],
                    'expected_date' => $header['expected_date'],
                    'received_date' => $header['received_date'],
                    'status' => $header['status'],
                    'subtotal' => $header['subtotal'],
                    'discount_amount' => $header['discount_amount'],
                    'tax_amount' => $header['tax_amount'],
                    'grand_total' => $header['grand_total'],
                    'notes' => $header['notes'],
                ]);
            }

            $procurementId = (int) $this->db->lastInsertId();
            $itemStatement = $this->db->prepare('INSERT INTO procurement_items (procurement_id, product_id, quantity, unit_cost, line_total, created_at)
                VALUES (:procurement_id, :product_id, :quantity, :unit_cost, :line_total, NOW())');

            foreach ($items as $item) {
                $itemStatement->execute([
                    'procurement_id' => $procurementId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                ]);
            }

            if ($header['status'] === 'received') {
                $this->applyReceipt($procurementId, (int) $header['user_id']);
            }

            if ($creditSchemaEnabled && $header['payment_method'] !== 'credit') {
                $paymentStatement = $this->db->prepare('INSERT INTO procurement_payments (procurement_id, payment_number, payment_date, amount, method, reference, notes, recorded_by, deleted_at, created_at, updated_at)
                    VALUES (:procurement_id, :payment_number, :payment_date, :amount, :method, :reference, :notes, :recorded_by, NULL, NOW(), NOW())');
                $paymentStatement->execute([
                    'procurement_id' => $procurementId,
                    'payment_number' => $header['payment_number'],
                    'payment_date' => $header['procurement_date'],
                    'amount' => $header['grand_total'],
                    'method' => $header['payment_method'],
                    'reference' => null,
                    'notes' => 'Règlement initial à la création de l’approvisionnement.',
                    'recorded_by' => $header['user_id'],
                ]);
            }

            if ($creditSchemaEnabled) {
                $this->refreshPaymentStatus($procurementId);
            }

            $this->db->commit();
            return $procurementId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function updateWithItems(int $id, array $header, array $items): void
    {
        $this->db->beginTransaction();

        try {
            $existing = $this->find($id);

            if (!$existing) {
                throw new RuntimeException('Approvisionnement introuvable.');
            }

            $creditSchemaEnabled = $this->supportsCreditTracking();
            if (!$creditSchemaEnabled && $header['payment_method'] === 'credit') {
                throw new RuntimeException('La base de donnees doit etre migree avant d utiliser les approvisionnements a credit.');
            }

            if ($creditSchemaEnabled && !$this->canEditPaymentsState($id, $existing)) {
                throw new RuntimeException('Cet approvisionnement ne peut plus être modifié car des règlements ont déjà été enregistrés.');
            }

            $existingItems = $this->items($id);
            $wasReceived = (string) ($existing['status'] ?? '') === 'received';
            $willBeReceived = (string) ($header['status'] ?? '') === 'received';

            if ($wasReceived) {
                $this->reverseReceipt($existingItems, $id, (int) $header['user_id']);
            }

            $receivedDate = null;
            if ($willBeReceived) {
                $receivedDate = (string) ($existing['received_date'] ?? '') !== ''
                    ? (string) $existing['received_date']
                    : date('Y-m-d');
            }

            if ($creditSchemaEnabled) {
                $statement = $this->db->prepare('UPDATE procurements SET
                        supplier_id = :supplier_id,
                        procurement_date = :procurement_date,
                        expected_date = :expected_date,
                        received_date = :received_date,
                        status = :status,
                        payment_method = :payment_method,
                        payment_status = :payment_status,
                        amount_paid = :amount_paid,
                        balance_due = :balance_due,
                        settled_at = :settled_at,
                        subtotal = :subtotal,
                        discount_amount = :discount_amount,
                        tax_amount = :tax_amount,
                        grand_total = :grand_total,
                        notes = :notes,
                        updated_at = NOW()
                    WHERE id = :id AND deleted_at IS NULL');
                $statement->execute([
                    'id' => $id,
                    'supplier_id' => $header['supplier_id'],
                    'procurement_date' => $header['procurement_date'],
                    'expected_date' => $header['expected_date'],
                    'received_date' => $receivedDate,
                    'status' => $header['status'],
                    'payment_method' => $header['payment_method'],
                    'payment_status' => $header['payment_method'] === 'credit' ? 'unpaid' : 'paid',
                    'amount_paid' => $header['payment_method'] === 'credit' ? 0 : $header['grand_total'],
                    'balance_due' => $header['payment_method'] === 'credit' ? $header['grand_total'] : 0,
                    'settled_at' => $header['payment_method'] === 'credit' ? null : date('Y-m-d H:i:s'),
                    'subtotal' => $header['subtotal'],
                    'discount_amount' => $header['discount_amount'],
                    'tax_amount' => $header['tax_amount'],
                    'grand_total' => $header['grand_total'],
                    'notes' => $header['notes'],
                ]);
            } else {
                $statement = $this->db->prepare('UPDATE procurements SET
                        supplier_id = :supplier_id,
                        procurement_date = :procurement_date,
                        expected_date = :expected_date,
                    received_date = :received_date,
                        status = :status,
                        subtotal = :subtotal,
                        discount_amount = :discount_amount,
                        tax_amount = :tax_amount,
                        grand_total = :grand_total,
                        notes = :notes,
                        updated_at = NOW()
                    WHERE id = :id AND deleted_at IS NULL');
                $statement->execute([
                    'id' => $id,
                    'supplier_id' => $header['supplier_id'],
                    'procurement_date' => $header['procurement_date'],
                    'expected_date' => $header['expected_date'],
                    'received_date' => $receivedDate,
                    'status' => $header['status'],
                    'subtotal' => $header['subtotal'],
                    'discount_amount' => $header['discount_amount'],
                    'tax_amount' => $header['tax_amount'],
                    'grand_total' => $header['grand_total'],
                    'notes' => $header['notes'],
                ]);
            }

            $deleteItemsStatement = $this->db->prepare('DELETE FROM procurement_items WHERE procurement_id = :procurement_id');
            $deleteItemsStatement->execute(['procurement_id' => $id]);

            $itemStatement = $this->db->prepare('INSERT INTO procurement_items (procurement_id, product_id, quantity, unit_cost, line_total, created_at)
                VALUES (:procurement_id, :product_id, :quantity, :unit_cost, :line_total, NOW())');

            foreach ($items as $item) {
                $itemStatement->execute([
                    'procurement_id' => $id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                ]);
            }

            if ($willBeReceived) {
                $this->applyReceipt($id, (int) $header['user_id']);
            }

            if ($creditSchemaEnabled) {
                $this->db->prepare('UPDATE procurement_payments SET deleted_at = NOW(), updated_at = NOW() WHERE procurement_id = :procurement_id AND deleted_at IS NULL')
                    ->execute(['procurement_id' => $id]);

                if ($header['payment_method'] !== 'credit') {
                    $paymentStatement = $this->db->prepare('INSERT INTO procurement_payments (procurement_id, payment_number, payment_date, amount, method, reference, notes, recorded_by, deleted_at, created_at, updated_at)
                        VALUES (:procurement_id, :payment_number, :payment_date, :amount, :method, :reference, :notes, :recorded_by, NULL, NOW(), NOW())');
                    $paymentStatement->execute([
                        'procurement_id' => $id,
                        'payment_number' => $header['payment_number'],
                        'payment_date' => $header['procurement_date'],
                        'amount' => $header['grand_total'],
                        'method' => $header['payment_method'],
                        'reference' => null,
                        'notes' => 'Règlement initial recalculé après modification de l’approvisionnement.',
                        'recorded_by' => $header['user_id'],
                    ]);
                }

                $this->refreshPaymentStatus($id);
            }

            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    public function markReceived(int $id, int $userId): void
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare("UPDATE procurements SET status = 'received', received_date = CURDATE(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL AND status <> 'received'");
            $statement->execute(['id' => $id]);

            if ($statement->rowCount() === 0) {
                $this->db->rollBack();
                return;
            }

            $this->applyReceipt($id, $userId);
            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function softCancel(int $id): void
    {
        $statement = $this->db->prepare("UPDATE procurements SET status = 'cancelled', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL");
        $statement->execute(['id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $procurement = $this->find($id);

        if (!$procurement) {
            throw new RuntimeException('Approvisionnement introuvable.');
        }

        if (in_array((string) $procurement['status'], ['received', 'cancelled'], true)) {
            throw new RuntimeException('Seuls les approvisionnements non reçus peuvent être supprimés.');
        }

        $statement = $this->db->prepare('UPDATE procurements SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);

        if ($this->supportsCreditTracking()) {
            $this->db->prepare('UPDATE procurement_payments SET deleted_at = NOW(), updated_at = NOW() WHERE procurement_id = :procurement_id AND deleted_at IS NULL')
                ->execute(['procurement_id' => $id]);
        }
    }

    public function refreshPaymentStatus(int $id): void
    {
        if (!$this->supportsCreditTracking()) {
            return;
        }

        $statement = $this->db->prepare('SELECT pr.grand_total, COALESCE(SUM(pp.amount), 0) AS paid
            FROM procurements pr
            LEFT JOIN procurement_payments pp ON pp.procurement_id = pr.id AND pp.deleted_at IS NULL
            WHERE pr.id = :id AND pr.deleted_at IS NULL
            GROUP BY pr.id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if (!$row) {
            return;
        }

        $total = (float) $row['grand_total'];
        $paid = (float) $row['paid'];
        $balance = max($total - $paid, 0);

        $paymentStatus = 'unpaid';
        $settledAt = null;
        if ($paid >= $total && $total > 0) {
            $paymentStatus = 'paid';
            $settledAt = date('Y-m-d H:i:s');
        } elseif ($paid > 0) {
            $paymentStatus = 'partial_paid';
        }

        $update = $this->db->prepare('UPDATE procurements SET amount_paid = :amount_paid, balance_due = :balance_due, payment_status = :payment_status, settled_at = :settled_at, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'amount_paid' => $paid,
            'balance_due' => $balance,
            'payment_status' => $paymentStatus,
            'settled_at' => $settledAt,
            'id' => $id,
        ]);
    }

    public function supportsCreditTracking(): bool
    {
        $columnStatement = $this->db->query("SHOW COLUMNS FROM procurements LIKE 'balance_due'");
        $hasBalanceDue = (bool) $columnStatement->fetch();

        $tableStatement = $this->db->query("SHOW TABLES LIKE 'procurement_payments'");
        $hasPaymentsTable = (bool) $tableStatement->fetch();

        return $hasBalanceDue && $hasPaymentsTable;
    }

    private function applyReceipt(int $procurementId, int $userId): void
    {
        $items = $this->items($procurementId);
        $productModel = new Product();
        $movementModel = new StockMovement();

        foreach ($items as $item) {
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
                'movement_type' => 'procurement_receipt',
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => 'procurement',
                'reference_id' => $procurementId,
                'note' => 'Réception approvisionnement #' . $procurementId,
                'movement_date' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
            ]);
        }
    }

    private function reverseReceipt(array $items, int $procurementId, int $userId): void
    {
        $productModel = new Product();
        $movementModel = new StockMovement();

        foreach ($items as $item) {
            $product = $productModel->find((int) $item['product_id']);
            if (!$product) {
                continue;
            }

            $before = (float) $product['current_stock'];
            $quantity = (float) $item['quantity'];
            $after = $before - $quantity;

            $productModel->adjustStock((int) $product['id'], $after);
            $movementModel->create([
                'product_id' => (int) $product['id'],
                'movement_type' => 'adjustment',
                'quantity' => -$quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => 'procurement',
                'reference_id' => $procurementId,
                'note' => 'Annulation technique de la réception avant modification approvisionnement #' . $procurementId,
                'movement_date' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
            ]);
        }
    }

    private function canEditPaymentsState(int $id, array $existing): bool
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM procurement_payments WHERE procurement_id = :procurement_id AND deleted_at IS NULL');
        $statement->execute(['procurement_id' => $id]);
        $paymentCount = (int) $statement->fetchColumn();

        if (($existing['payment_method'] ?? 'cash') === 'credit') {
            return $paymentCount === 0;
        }

        return $paymentCount <= 1 && (float) ($existing['amount_paid'] ?? 0) >= (float) ($existing['grand_total'] ?? 0);
    }
}
