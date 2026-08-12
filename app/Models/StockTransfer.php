<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

final class StockTransfer extends Model
{
    public function recent(?int $shopId = null): array
    {
        $sql = 'SELECT st.*, p.sku, p.name AS product_name,
                    ss.name AS source_shop_name,
                    ds.name AS destination_shop_name,
                    u.full_name AS user_name
                FROM stock_transfers st
                INNER JOIN products p ON p.id = st.product_id
                LEFT JOIN shops ss ON ss.id = st.source_shop_id
                LEFT JOIN shops ds ON ds.id = st.destination_shop_id
                LEFT JOIN users u ON u.id = st.created_by
                WHERE st.deleted_at IS NULL';
        $params = [];

        if ($shopId !== null) {
            $sql .= ' AND (st.destination_shop_id = :shop_id OR st.source_shop_id = :shop_id_source)';
            $params['shop_id'] = $shopId;
            $params['shop_id_source'] = $shopId;
        }

        $sql .= ' ORDER BY st.id DESC LIMIT 25';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function transferManyToShop(int $destinationShopId, array $items, string $note, ?int $userId): int
    {
        if ($items === []) {
            throw new \RuntimeException('Aucun produit à transférer.');
        }

        $this->db->beginTransaction();

        try {
            $count = 0;
            foreach ($items as $item) {
                $this->transferToShopInTransaction((int) $item['product_id'], $destinationShopId, (float) $item['quantity'], $note, $userId);
                $count++;
            }

            $this->db->commit();

            return $count;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    public function returnManyToCentral(int $sourceShopId, array $items, string $note, ?int $userId): int
    {
        if ($items === []) {
            throw new \RuntimeException('Aucun produit à retourner.');
        }

        $this->db->beginTransaction();

        try {
            $count = 0;
            foreach ($items as $item) {
                $this->returnToCentralInTransaction($sourceShopId, (int) $item['product_id'], (float) $item['quantity'], $note, $userId);
                $count++;
            }

            $this->db->commit();

            return $count;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    public function transferToShop(int $productId, int $destinationShopId, float $quantity, string $note, ?int $userId): int
    {
        if ($quantity <= 0) {
            throw new \RuntimeException('La quantité à transférer doit être strictement positive.');
        }

        $this->db->beginTransaction();

        try {
            $transferId = $this->transferToShopInTransaction($productId, $destinationShopId, $quantity, $note, $userId);

            $this->db->commit();

            return $transferId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    private function transferToShopInTransaction(int $productId, int $destinationShopId, float $quantity, string $note, ?int $userId): int
    {
        if ($quantity <= 0) {
            throw new \RuntimeException('La quantité à transférer doit être strictement positive.');
        }

        $productStatement = $this->db->prepare('SELECT id, name, current_stock FROM products WHERE id = :id AND deleted_at IS NULL FOR UPDATE');
        $productStatement->execute(['id' => $productId]);
        $product = $productStatement->fetch();

        if (!$product) {
            throw new \RuntimeException('Produit introuvable.');
        }

        $shopStatement = $this->db->prepare('SELECT id, name FROM shops WHERE id = :id AND deleted_at IS NULL AND is_active = 1 LIMIT 1');
        $shopStatement->execute(['id' => $destinationShopId]);
        $shop = $shopStatement->fetch();

        if (!$shop) {
            throw new \RuntimeException('Boutique de destination introuvable ou inactive.');
        }

        $centralBefore = (float) $product['current_stock'];
        $centralAfter = $centralBefore - $quantity;

        if ($centralAfter < 0) {
            throw new \RuntimeException('Stock général insuffisant pour ' . $product['name'] . '.');
        }

        $shopBefore = $this->shopStockForUpdate($productId, $destinationShopId);
        $shopAfter = $shopBefore + $quantity;

        $this->db->prepare('UPDATE products SET current_stock = :current_stock, updated_at = NOW() WHERE id = :id')->execute([
            'current_stock' => $centralAfter,
            'id' => $productId,
        ]);

        $this->upsertShopStock($productId, $destinationShopId, $shopAfter);

        $transferId = $this->createTransfer($productId, null, $destinationShopId, 'to_shop', $quantity, $note, $userId);
        $movementModel = new StockMovement();
        $movementDate = date('Y-m-d H:i:s');
        $movementModel->create([
            'product_id' => $productId,
            'movement_type' => 'transfer_out',
            'quantity' => -$quantity,
            'quantity_before' => $centralBefore,
            'quantity_after' => $centralAfter,
            'source_shop_id' => null,
            'destination_shop_id' => $destinationShopId,
            'reference_type' => 'stock_transfer',
            'reference_id' => $transferId,
            'note' => $note !== '' ? $note : 'Transfert vers ' . $shop['name'],
            'movement_date' => $movementDate,
            'created_by' => $userId,
        ]);
        $movementModel->create([
            'product_id' => $productId,
            'movement_type' => 'transfer_in',
            'quantity' => $quantity,
            'quantity_before' => $shopBefore,
            'quantity_after' => $shopAfter,
            'source_shop_id' => null,
            'destination_shop_id' => $destinationShopId,
            'reference_type' => 'stock_transfer',
            'reference_id' => $transferId,
            'note' => $note !== '' ? $note : 'Réception depuis le stock général',
            'movement_date' => $movementDate,
            'created_by' => $userId,
        ]);

        return $transferId;
    }

    private function returnToCentralInTransaction(int $sourceShopId, int $productId, float $quantity, string $note, ?int $userId): int
    {
        if ($quantity <= 0) {
            throw new \RuntimeException('La quantité à retourner doit être strictement positive.');
        }

        $shopStatement = $this->db->prepare('SELECT id, name FROM shops WHERE id = :id AND deleted_at IS NULL AND is_active = 1 LIMIT 1');
        $shopStatement->execute(['id' => $sourceShopId]);
        $shop = $shopStatement->fetch();

        if (!$shop) {
            throw new \RuntimeException('Boutique source introuvable ou inactive.');
        }

        $productStatement = $this->db->prepare('SELECT id, name, current_stock FROM products WHERE id = :id AND deleted_at IS NULL FOR UPDATE');
        $productStatement->execute(['id' => $productId]);
        $product = $productStatement->fetch();

        if (!$product) {
            throw new \RuntimeException('Produit introuvable.');
        }

        $shopBefore = $this->shopStockForUpdate($productId, $sourceShopId);
        $shopAfter = $shopBefore - $quantity;

        if ($shopAfter < 0) {
            throw new \RuntimeException('Stock boutique insuffisant pour ' . $product['name'] . '.');
        }

        $centralBefore = (float) $product['current_stock'];
        $centralAfter = $centralBefore + $quantity;

        $this->upsertShopStock($productId, $sourceShopId, $shopAfter);
        $this->db->prepare('UPDATE products SET current_stock = :current_stock, updated_at = NOW() WHERE id = :id')->execute([
            'current_stock' => $centralAfter,
            'id' => $productId,
        ]);

        $transferId = $this->createTransfer($productId, $sourceShopId, null, 'to_central', $quantity, $note, $userId);
        $movementModel = new StockMovement();
        $movementDate = date('Y-m-d H:i:s');
        $movementModel->create([
            'product_id' => $productId,
            'movement_type' => 'transfer_out',
            'quantity' => -$quantity,
            'quantity_before' => $shopBefore,
            'quantity_after' => $shopAfter,
            'source_shop_id' => $sourceShopId,
            'destination_shop_id' => null,
            'reference_type' => 'stock_return',
            'reference_id' => $transferId,
            'note' => $note !== '' ? $note : 'Retour vers stock général',
            'movement_date' => $movementDate,
            'created_by' => $userId,
        ]);
        $movementModel->create([
            'product_id' => $productId,
            'movement_type' => 'transfer_in',
            'quantity' => $quantity,
            'quantity_before' => $centralBefore,
            'quantity_after' => $centralAfter,
            'source_shop_id' => $sourceShopId,
            'destination_shop_id' => null,
            'reference_type' => 'stock_return',
            'reference_id' => $transferId,
            'note' => $note !== '' ? $note : 'Retour reçu depuis ' . $shop['name'],
            'movement_date' => $movementDate,
            'created_by' => $userId,
        ]);

        return $transferId;
    }

    private function shopStockForUpdate(int $productId, int $shopId): float
    {
        $statement = $this->db->prepare('SELECT current_stock FROM product_stocks WHERE product_id = :product_id AND shop_id = :shop_id FOR UPDATE');
        $statement->execute([
            'product_id' => $productId,
            'shop_id' => $shopId,
        ]);

        return (float) ($statement->fetchColumn() ?: 0);
    }

    private function upsertShopStock(int $productId, int $shopId, float $newStock): void
    {
        $upsert = $this->db->prepare('INSERT INTO product_stocks (product_id, shop_id, current_stock, created_at, updated_at)
            VALUES (:product_id, :shop_id, :current_stock, NOW(), NOW())
            ON DUPLICATE KEY UPDATE current_stock = VALUES(current_stock), updated_at = NOW()');
        $upsert->execute([
            'product_id' => $productId,
            'shop_id' => $shopId,
            'current_stock' => $newStock,
        ]);
    }

    private function createTransfer(int $productId, ?int $sourceShopId, ?int $destinationShopId, string $transferType, float $quantity, string $note, ?int $userId): int
    {
        $transferStatement = $this->db->prepare('INSERT INTO stock_transfers (product_id, source_shop_id, destination_shop_id, transfer_type, quantity, note, created_by, created_at, updated_at)
            VALUES (:product_id, :source_shop_id, :destination_shop_id, :transfer_type, :quantity, :note, :created_by, NOW(), NOW())');
        $transferStatement->execute([
            'product_id' => $productId,
            'source_shop_id' => $sourceShopId,
            'destination_shop_id' => $destinationShopId,
            'transfer_type' => $transferType,
            'quantity' => $quantity,
            'note' => $note,
            'created_by' => $userId,
        ]);

        return (int) $this->db->lastInsertId();
    }
}