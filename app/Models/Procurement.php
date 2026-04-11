<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
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
            $statement = $this->db->prepare('INSERT INTO procurements (supplier_id, user_id, procurement_number, procurement_date, expected_date, received_date, status, subtotal, discount_amount, tax_amount, grand_total, notes, deleted_at, created_at, updated_at)
                VALUES (:supplier_id, :user_id, :procurement_number, :procurement_date, :expected_date, :received_date, :status, :subtotal, :discount_amount, :tax_amount, :grand_total, :notes, NULL, NOW(), NOW())');
            $statement->execute($header);

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

            $this->db->commit();
            return $procurementId;
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
}
