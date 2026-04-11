<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Product extends Model
{
    public function all(): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, u.name AS unit_name, u.symbol AS unit_symbol
                FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                INNER JOIN units u ON u.id = p.unit_id
                WHERE p.deleted_at IS NULL
                ORDER BY p.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM products WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();
        return $product ?: null;
    }

    public function options(): array
    {
        $statement = $this->db->query('SELECT p.id, p.sku, p.name, p.current_stock, p.cost_price, p.sale_price, u.symbol AS unit_symbol FROM products p INNER JOIN units u ON u.id = p.unit_id WHERE p.deleted_at IS NULL AND p.is_active = 1 ORDER BY p.name ASC');
        return $statement->fetchAll();
    }

    public function lowStock(): array
    {
        $statement = $this->db->query('SELECT id, sku, name, current_stock, minimum_stock FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock <= minimum_stock ORDER BY current_stock ASC, name ASC');
        return $statement->fetchAll();
    }

    public function create(array $data): void
    {
        $sql = 'INSERT INTO products (category_id, unit_id, sku, name, description, barcode, cost_price, sale_price, minimum_stock, current_stock, image_path, is_active, created_at, updated_at)
                VALUES (:category_id, :unit_id, :sku, :name, :description, :barcode, :cost_price, :sale_price, :minimum_stock, :current_stock, :image_path, :is_active, NOW(), NOW())';

        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function updateProduct(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE products SET
                    category_id = :category_id,
                    unit_id = :unit_id,
                    sku = :sku,
                    name = :name,
                    description = :description,
                    barcode = :barcode,
                    cost_price = :cost_price,
                    sale_price = :sale_price,
                    minimum_stock = :minimum_stock,
                    current_stock = :current_stock,
                    image_path = :image_path,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL';

        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE products SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function adjustStock(int $id, float $newStock): void
    {
        $statement = $this->db->prepare('UPDATE products SET current_stock = :current_stock, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute([
            'current_stock' => $newStock,
            'id' => $id,
        ]);
    }
}
