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
                    u.full_name AS user_name,
                    ru.full_name AS received_by_name,
                    CASE
                        WHEN st.status = \'pending\' AND EXISTS (
                            SELECT 1
                            FROM stock_movements sm
                            WHERE sm.reference_id = st.id
                              AND sm.reference_type IN (\'stock_transfer\', \'stock_return\')
                              AND sm.movement_type IN (\'transfer_out\', \'transfer_in\')
                        ) THEN \'received\'
                        ELSE st.status
                    END AS effective_status
                FROM stock_transfers st
                INNER JOIN products p ON p.id = st.product_id
                LEFT JOIN shops ss ON ss.id = st.source_shop_id
                LEFT JOIN shops ds ON ds.id = st.destination_shop_id
                LEFT JOIN users u ON u.id = st.created_by
                LEFT JOIN users ru ON ru.id = st.received_by
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

    public function pendingForReception(?int $shopId, bool $canManageCentral): array
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
                WHERE st.deleted_at IS NULL
                                    AND st.status = :status
                                    AND NOT EXISTS (
                                            SELECT 1
                                            FROM stock_movements sm
                                            WHERE sm.reference_id = st.id
                                                AND sm.reference_type IN (\'stock_transfer\', \'stock_return\')
                                                AND sm.movement_type IN (\'transfer_out\', \'transfer_in\')
                                    )';
        $params = ['status' => 'pending'];

        if ($shopId !== null) {
            $sql .= ' AND st.transfer_type = :to_shop AND st.destination_shop_id = :shop_id';
            $params['to_shop'] = 'to_shop';
            $params['shop_id'] = $shopId;
        } elseif ($canManageCentral) {
            $sql .= ' AND st.transfer_type = :to_central';
            $params['to_central'] = 'to_central';
        } else {
            return [];
        }

        $sql .= ' ORDER BY st.id DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function pendingCountForReception(?int $shopId, bool $canManageCentral): int
    {
        $sql = 'SELECT COUNT(*)
                FROM stock_transfers st
                WHERE st.deleted_at IS NULL
                                    AND st.status = :status
                                    AND NOT EXISTS (
                                            SELECT 1
                                            FROM stock_movements sm
                                            WHERE sm.reference_id = st.id
                                                AND sm.reference_type IN (\'stock_transfer\', \'stock_return\')
                                                AND sm.movement_type IN (\'transfer_out\', \'transfer_in\')
                                    )';
        $params = ['status' => 'pending'];

        if ($shopId !== null) {
            $sql .= ' AND st.transfer_type = :to_shop AND st.destination_shop_id = :shop_id';
            $params['to_shop'] = 'to_shop';
            $params['shop_id'] = $shopId;
        } elseif ($canManageCentral) {
            $sql .= ' AND st.transfer_type = :to_central';
            $params['to_central'] = 'to_central';
        } else {
            return 0;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
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

        $productStatement = $this->db->prepare('SELECT id, name, current_stock FROM products WHERE id = :id AND deleted_at IS NULL');
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

        return $this->createTransfer($productId, null, $destinationShopId, 'to_shop', $quantity, $note, $userId, 'pending');
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

        $productStatement = $this->db->prepare('SELECT id, name, current_stock FROM products WHERE id = :id AND deleted_at IS NULL');
        $productStatement->execute(['id' => $productId]);
        $product = $productStatement->fetch();

        if (!$product) {
            throw new \RuntimeException('Produit introuvable.');
        }

        return $this->createTransfer($productId, $sourceShopId, null, 'to_central', $quantity, $note, $userId, 'pending');
    }

    public function receivePending(int $transferId, ?int $receiverUserId, ?int $receiverShopId, bool $canManageCentral): void
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare('SELECT * FROM stock_transfers WHERE id = :id AND deleted_at IS NULL FOR UPDATE');
            $statement->execute(['id' => $transferId]);
            $transfer = $statement->fetch();

            if (!$transfer) {
                throw new \RuntimeException('Transfert introuvable.');
            }

            if ((string) ($transfer['status'] ?? 'pending') !== 'pending') {
                throw new \RuntimeException('Ce transfert a déjà été réceptionné.');
            }

            $transferType = (string) ($transfer['transfer_type'] ?? 'to_shop');

            if ($transferType === 'to_shop') {
                if ($receiverShopId === null || (int) $transfer['destination_shop_id'] !== $receiverShopId) {
                    throw new \RuntimeException('Seule la boutique destinataire peut valider cette réception.');
                }
            } elseif ($transferType === 'to_central') {
                if (!$canManageCentral) {
                    throw new \RuntimeException('Seul le gestionnaire du stock général peut valider ce retour.');
                }
            }

            $this->applyPendingTransfer((array) $transfer, $receiverUserId);

            $update = $this->db->prepare('UPDATE stock_transfers
                SET status = :status,
                    received_at = NOW(),
                    received_by = :received_by,
                    updated_at = NOW()
                WHERE id = :id');
            $update->execute([
                'status' => 'received',
                'received_by' => $receiverUserId,
                'id' => $transferId,
            ]);

            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    private function applyPendingTransfer(array $transfer, ?int $receiverUserId): void
    {
        $productId = (int) $transfer['product_id'];
        $quantity = (float) $transfer['quantity'];
        $transferType = (string) ($transfer['transfer_type'] ?? 'to_shop');

        if ($quantity <= 0) {
            throw new \RuntimeException('Quantité de transfert invalide.');
        }

        $productStatement = $this->db->prepare('SELECT id, name, current_stock FROM products WHERE id = :id AND deleted_at IS NULL FOR UPDATE');
        $productStatement->execute(['id' => $productId]);
        $product = $productStatement->fetch();

        if (!$product) {
            throw new \RuntimeException('Produit introuvable.');
        }

        if ($transferType === 'to_shop') {
            $destinationShopId = (int) ($transfer['destination_shop_id'] ?? 0);
            $shopStatement = $this->db->prepare('SELECT id, name FROM shops WHERE id = :id AND deleted_at IS NULL AND is_active = 1 LIMIT 1');
            $shopStatement->execute(['id' => $destinationShopId]);
            $shop = $shopStatement->fetch();

            if (!$shop) {
                throw new \RuntimeException('Boutique destinataire introuvable ou inactive.');
            }

            $centralBefore = (float) $product['current_stock'];
            $centralAfter = $centralBefore - $quantity;
            if ($centralAfter < 0) {
                throw new \RuntimeException('Stock général insuffisant pour finaliser cette réception.');
            }

            $shopBefore = $this->shopStockForUpdate($productId, $destinationShopId);
            $shopAfter = $shopBefore + $quantity;

            $this->db->prepare('UPDATE products SET current_stock = :current_stock, updated_at = NOW() WHERE id = :id')->execute([
                'current_stock' => $centralAfter,
                'id' => $productId,
            ]);
            $this->upsertShopStock($productId, $destinationShopId, $shopAfter);

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
                'reference_id' => (int) $transfer['id'],
                'note' => (string) ($transfer['note'] ?: ('Transfert vers ' . $shop['name'])),
                'movement_date' => $movementDate,
                'created_by' => $receiverUserId,
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
                'reference_id' => (int) $transfer['id'],
                'note' => (string) ($transfer['note'] ?: 'Réception depuis le stock général'),
                'movement_date' => $movementDate,
                'created_by' => $receiverUserId,
            ]);

            return;
        }

        if ($transferType !== 'to_central') {
            throw new \RuntimeException('Type de transfert invalide.');
        }

        $sourceShopId = (int) ($transfer['source_shop_id'] ?? 0);
        $shopStatement = $this->db->prepare('SELECT id, name FROM shops WHERE id = :id AND deleted_at IS NULL AND is_active = 1 LIMIT 1');
        $shopStatement->execute(['id' => $sourceShopId]);
        $shop = $shopStatement->fetch();

        if (!$shop) {
            throw new \RuntimeException('Boutique source introuvable ou inactive.');
        }

        $shopBefore = $this->shopStockForUpdate($productId, $sourceShopId);
        $shopAfter = $shopBefore - $quantity;

        if ($shopAfter < 0) {
            throw new \RuntimeException('Stock boutique insuffisant pour finaliser ce retour.');
        }

        $centralBefore = (float) $product['current_stock'];
        $centralAfter = $centralBefore + $quantity;

        $this->upsertShopStock($productId, $sourceShopId, $shopAfter);
        $this->db->prepare('UPDATE products SET current_stock = :current_stock, updated_at = NOW() WHERE id = :id')->execute([
            'current_stock' => $centralAfter,
            'id' => $productId,
        ]);

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
            'reference_id' => (int) $transfer['id'],
            'note' => (string) ($transfer['note'] ?: 'Retour vers stock général'),
            'movement_date' => $movementDate,
            'created_by' => $receiverUserId,
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
            'reference_id' => (int) $transfer['id'],
            'note' => (string) ($transfer['note'] ?: ('Retour reçu depuis ' . $shop['name'])),
            'movement_date' => $movementDate,
            'created_by' => $receiverUserId,
        ]);
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

    private function createTransfer(int $productId, ?int $sourceShopId, ?int $destinationShopId, string $transferType, float $quantity, string $note, ?int $userId, string $status = 'pending'): int
    {
        $transferStatement = $this->db->prepare('INSERT INTO stock_transfers (product_id, source_shop_id, destination_shop_id, transfer_type, quantity, note, status, requested_at, created_by, created_at, updated_at)
            VALUES (:product_id, :source_shop_id, :destination_shop_id, :transfer_type, :quantity, :note, :status, NOW(), :created_by, NOW(), NOW())');
        $transferStatement->execute([
            'product_id' => $productId,
            'source_shop_id' => $sourceShopId,
            'destination_shop_id' => $destinationShopId,
            'transfer_type' => $transferType,
            'quantity' => $quantity,
            'note' => $note,
            'status' => $status,
            'created_by' => $userId,
        ]);

        return (int) $this->db->lastInsertId();
    }
}