<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

final class Quote extends Model
{
    public function all(): array
    {
        $sql = 'SELECT q.*, c.company_name AS client_name, u.full_name AS user_name
                FROM quotes q
                INNER JOIN clients c ON c.id = q.client_id
                INNER JOIN users u ON u.id = q.created_by
                WHERE q.deleted_at IS NULL
                ORDER BY q.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT q.*, c.company_name AS client_name, c.contact_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address, c.city AS client_city, u.full_name AS user_name
                FROM quotes q
                INNER JOIN clients c ON c.id = q.client_id
                INNER JOIN users u ON u.id = q.created_by
                WHERE q.id = :id AND q.deleted_at IS NULL
                LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $quote = $statement->fetch();
        return $quote ?: null;
    }

    public function items(int $quoteId): array
    {
        $sql = 'SELECT qi.*, p.sku, p.name AS product_name, s.code AS service_code, s.name AS service_name
                FROM quote_items qi
                LEFT JOIN products p ON p.id = qi.product_id
                LEFT JOIN services s ON s.id = qi.service_id
                WHERE qi.quote_id = :quote_id
                ORDER BY qi.id ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['quote_id' => $quoteId]);
        return $statement->fetchAll();
    }

    public function createWithItems(array $header, array $items): int
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare('INSERT INTO quotes (client_id, quote_number, quote_date, valid_until, status, subtotal, discount_amount, tax_amount, grand_total, notes, created_by, deleted_at, created_at, updated_at)
                VALUES (:client_id, :quote_number, :quote_date, :valid_until, :status, :subtotal, :discount_amount, :tax_amount, :grand_total, :notes, :created_by, NULL, NOW(), NOW())');
            $statement->execute($header);
            $quoteId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare('INSERT INTO quote_items (quote_id, item_type, product_id, service_id, description, quantity, unit_price, discount_amount, tax_amount, line_total, created_at)
                VALUES (:quote_id, :item_type, :product_id, :service_id, :description, :quantity, :unit_price, :discount_amount, :tax_amount, :line_total, NOW())');

            foreach ($items as $item) {
                $itemStatement->execute([
                    'quote_id' => $quoteId,
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
            return $quoteId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function markConverted(int $id): void
    {
        $statement = $this->db->prepare("UPDATE quotes SET status = 'converted', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL");
        $statement->execute(['id' => $id]);
    }

    public function softCancel(int $id): void
    {
        $statement = $this->db->prepare("UPDATE quotes SET status = 'cancelled', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL");
        $statement->execute(['id' => $id]);
    }
}
