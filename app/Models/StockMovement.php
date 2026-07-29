<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class StockMovement extends Model
{
    public function all(): array
    {
        $sql = 'SELECT sm.*, p.sku, p.name AS product_name, u.full_name AS user_name,
                   ss.name AS source_shop_name, ds.name AS destination_shop_name
                FROM stock_movements sm
                INNER JOIN products p ON p.id = sm.product_id
                LEFT JOIN users u ON u.id = sm.created_by
            LEFT JOIN shops ss ON ss.id = sm.source_shop_id
            LEFT JOIN shops ds ON ds.id = sm.destination_shop_id
                ORDER BY sm.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function create(array $data): void
    {
        $data += [
            'source_shop_id' => null,
            'destination_shop_id' => null,
        ];

        $sql = 'INSERT INTO stock_movements (product_id, movement_type, quantity, quantity_before, quantity_after, source_shop_id, destination_shop_id, reference_type, reference_id, note, movement_date, created_by, created_at)
            VALUES (:product_id, :movement_type, :quantity, :quantity_before, :quantity_after, :source_shop_id, :destination_shop_id, :reference_type, :reference_id, :note, :movement_date, :created_by, NOW())';

        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }
}
