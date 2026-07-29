<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

final class StockTransfer extends Model
{
    public function recent(?int $shopId = null): array
    {
        $sql = 'SELECT st.*, p.sku, p.name AS product_name, s.name AS destination_shop_name, u.full_name AS user_name
                FROM stock_transfers st
                INNER JOIN products p ON p.id = st.product_id
                INNER JOIN shops s ON s.id = st.destination_shop_id
                LEFT JOIN users u ON u.id = st.created_by
                WHERE st.deleted_at IS NULL';
        $params = [];

        if ($shopId !== null) {
            $sql .= ' AND st.destination_shop_id = :shop_id';
            $params['shop_id'] = $shopId;
        }

        $sql .= ' ORDER BY st.id DESC LIMIT 25';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function transferToShop(int $productId, int $destinationShopId, float $quantity, string $note, ?int $userId): int
    {
        if ($quantity <= 0) {
            throw new \RuntimeException('La quantité à transférer doit être strictement positive.');
        }

        $this->db->beginTransaction();

        try {
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
                throw new \RuntimeException('Stock général insuffisant pour ce transfert.');
            }

            $stockStatement = $this->db->prepare('SELECT current_stock FROM product_stocks WHERE product_id = :product_id AND shop_id = :shop_id FOR UPDATE');
            $stockStatement->execute([
                'product_id' => $productId,
                'shop_id' => $destinationShopId,
            ]);
            $shopStock = $stockStatement->fetch();
            $shopBefore = $shopStock ? (float) $shopStock['current_stock'] : 0.0;
            $shopAfter = $shopBefore + $quantity;

            $this->db->prepare('UPDATE products SET current_stock = :current_stock, updated_at = NOW() WHERE id = :id')->execute([
                'current_stock' => $centralAfter,
                'id' => $productId,
            ]);

            $upsert = $this->db->prepare('INSERT INTO product_stocks (product_id, shop_id, current_stock, created_at, updated_at)
                VALUES (:product_id, :shop_id, :current_stock, NOW(), NOW())
                ON DUPLICATE KEY UPDATE current_stock = VALUES(current_stock), updated_at = NOW()');
            $upsert->execute([
                'product_id' => $productId,
                'shop_id' => $destinationShopId,
                'current_stock' => $shopAfter,
            ]);

            $transferStatement = $this->db->prepare('INSERT INTO stock_transfers (product_id, destination_shop_id, quantity, note, created_by, created_at, updated_at)
                VALUES (:product_id, :destination_shop_id, :quantity, :note, :created_by, NOW(), NOW())');
            $transferStatement->execute([
                'product_id' => $productId,
                'destination_shop_id' => $destinationShopId,
                'quantity' => $quantity,
                'note' => $note,
                'created_by' => $userId,
            ]);
            $transferId = (int) $this->db->lastInsertId();

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

            $this->db->commit();

            return $transferId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }
}