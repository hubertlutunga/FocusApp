<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Product extends Model
{
    public function all(): array
    {
        return $this->allForStock(null);
    }

    public function allForStock(?int $shopId = null): array
    {
        if ($shopId !== null) {
            $sql = 'SELECT p.*, COALESCE(ps.current_stock, 0) AS current_stock, p.current_stock AS central_stock,
                           COALESCE(ps.minimum_stock, p.minimum_stock) AS minimum_stock,
                           c.name AS category_name, u.name AS unit_name, u.symbol AS unit_symbol, s.name AS stock_shop_name
                    FROM products p
                    INNER JOIN categories c ON c.id = p.category_id
                    INNER JOIN units u ON u.id = p.unit_id
                    INNER JOIN shops s ON s.id = :shop_id
                    LEFT JOIN product_stocks ps ON ps.product_id = p.id AND ps.shop_id = :shop_id_stock
                    WHERE p.deleted_at IS NULL
                    ORDER BY p.id DESC';

            $statement = $this->db->prepare($sql);
            $statement->execute([
                'shop_id' => $shopId,
                'shop_id_stock' => $shopId,
            ]);

            return $statement->fetchAll();
        }

        $sql = 'SELECT p.*, p.current_stock AS central_stock, c.name AS category_name, u.name AS unit_name, u.symbol AS unit_symbol, NULL AS stock_shop_name
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

    public function options(?int $shopId = null): array
    {
        if ($shopId !== null) {
            $statement = $this->db->prepare('SELECT p.id, p.sku, p.name, COALESCE(ps.current_stock, 0) AS current_stock, p.cost_price, p.sale_price, u.symbol AS unit_symbol
                FROM products p
                INNER JOIN units u ON u.id = p.unit_id
                LEFT JOIN product_stocks ps ON ps.product_id = p.id AND ps.shop_id = :shop_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1
                ORDER BY p.name ASC');
            $statement->execute(['shop_id' => $shopId]);

            return $statement->fetchAll();
        }

        $statement = $this->db->query('SELECT p.id, p.sku, p.name, p.current_stock, p.cost_price, p.sale_price, u.symbol AS unit_symbol FROM products p INNER JOIN units u ON u.id = p.unit_id WHERE p.deleted_at IS NULL AND p.is_active = 1 ORDER BY p.name ASC');
        return $statement->fetchAll();
    }

    public function stockForLocation(int $id, ?int $shopId = null): float
    {
        if ($shopId === null) {
            $product = $this->find($id);
            return $product ? (float) $product['current_stock'] : 0.0;
        }

        $statement = $this->db->prepare('SELECT current_stock FROM product_stocks WHERE product_id = :product_id AND shop_id = :shop_id LIMIT 1');
        $statement->execute([
            'product_id' => $id,
            'shop_id' => $shopId,
        ]);

        return (float) ($statement->fetchColumn() ?: 0);
    }

    public function lowStock(): array
    {
        $statement = $this->db->query('SELECT id, sku, name, current_stock, minimum_stock FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock <= minimum_stock ORDER BY current_stock ASC, name ASC');
        return $statement->fetchAll();
    }

    public function salesAnalysis(string $dateFrom, string $dateTo, ?int $productId = null): array
    {
        $periodSummaryStatement = $this->db->prepare(
            "SELECT
                (
                    SELECT COUNT(*)
                    FROM invoices i
                    WHERE i.deleted_at IS NULL
                      AND i.status IN ('validated', 'partial_paid', 'paid')
                      AND i.invoice_date BETWEEN :date_from_invoices AND :date_to_invoices
                ) AS invoice_count,
                (
                    SELECT COALESCE(SUM(i.grand_total), 0)
                    FROM invoices i
                    WHERE i.deleted_at IS NULL
                      AND i.status IN ('validated', 'partial_paid', 'paid')
                      AND i.invoice_date BETWEEN :date_from_sales AND :date_to_sales
                ) AS total_sales,
                (
                    SELECT COALESCE(SUM(ii.quantity), 0)
                    FROM invoice_items ii
                    INNER JOIN invoices i ON i.id = ii.invoice_id
                    WHERE i.deleted_at IS NULL
                      AND i.status IN ('validated', 'partial_paid', 'paid')
                      AND ii.item_type = 'product'
                      AND i.invoice_date BETWEEN :date_from_products AND :date_to_products
                ) AS product_quantity_sold,
                (
                    SELECT COALESCE(SUM(ii.line_total), 0)
                    FROM invoice_items ii
                    INNER JOIN invoices i ON i.id = ii.invoice_id
                    WHERE i.deleted_at IS NULL
                      AND i.status IN ('validated', 'partial_paid', 'paid')
                      AND ii.item_type = 'product'
                      AND i.invoice_date BETWEEN :date_from_product_amount AND :date_to_product_amount
                ) AS product_sales_amount"
        );
        $periodSummaryStatement->execute([
            'date_from_invoices' => $dateFrom,
            'date_to_invoices' => $dateTo,
            'date_from_sales' => $dateFrom,
            'date_to_sales' => $dateTo,
            'date_from_products' => $dateFrom,
            'date_to_products' => $dateTo,
            'date_from_product_amount' => $dateFrom,
            'date_to_product_amount' => $dateTo,
        ]);

        $topProductsStatement = $this->db->prepare(
            "SELECT p.id,
                    p.sku,
                    p.name,
                    u.symbol AS unit_symbol,
                    COALESCE(SUM(ii.quantity), 0) AS quantity_sold,
                    COALESCE(SUM(ii.line_total), 0) AS revenue
             FROM invoice_items ii
             INNER JOIN invoices i ON i.id = ii.invoice_id
             INNER JOIN products p ON p.id = ii.product_id
             INNER JOIN units u ON u.id = p.unit_id
             WHERE i.deleted_at IS NULL
               AND i.status IN ('validated', 'partial_paid', 'paid')
               AND ii.item_type = 'product'
               AND i.invoice_date BETWEEN :date_from AND :date_to
             GROUP BY p.id, p.sku, p.name, u.symbol
             ORDER BY quantity_sold DESC, revenue DESC, p.name ASC
             LIMIT 5"
        );
        $topProductsStatement->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $analysis = [
            'period_summary' => $periodSummaryStatement->fetch() ?: [
                'invoice_count' => 0,
                'total_sales' => 0,
                'product_quantity_sold' => 0,
                'product_sales_amount' => 0,
            ],
            'top_products' => $topProductsStatement->fetchAll(),
            'selected_product' => null,
            'product_summary' => null,
            'product_sales' => [],
        ];

        if ($productId === null) {
            return $analysis;
        }

        $productStatement = $this->db->prepare('SELECT p.*, c.name AS category_name, u.name AS unit_name, u.symbol AS unit_symbol
                FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                INNER JOIN units u ON u.id = p.unit_id
                WHERE p.id = :id AND p.deleted_at IS NULL
                LIMIT 1');
        $productStatement->execute(['id' => $productId]);
        $selectedProduct = $productStatement->fetch();

        if (!$selectedProduct) {
            return $analysis;
        }

        $periodStart = $dateFrom . ' 00:00:00';
        $periodEndExclusive = date('Y-m-d H:i:s', strtotime($dateTo . ' +1 day'));

        $productSummaryStatement = $this->db->prepare(
            "SELECT COUNT(DISTINCT i.id) AS invoice_count,
                    COALESCE(SUM(ii.quantity), 0) AS quantity_sold,
                    COALESCE(SUM(ii.line_total), 0) AS revenue,
                    COALESCE(MIN(i.invoice_date), '') AS first_sale_date,
                    COALESCE(MAX(i.invoice_date), '') AS last_sale_date
             FROM invoice_items ii
             INNER JOIN invoices i ON i.id = ii.invoice_id
             WHERE i.deleted_at IS NULL
               AND i.status IN ('validated', 'partial_paid', 'paid')
               AND ii.item_type = 'product'
               AND ii.product_id = :product_id
               AND i.invoice_date BETWEEN :date_from AND :date_to"
        );
        $productSummaryStatement->execute([
            'product_id' => $productId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        $productSummary = $productSummaryStatement->fetch() ?: [];

        $movementSummaryStatement = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END), 0) AS entries_quantity,
                    COALESCE(SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END), 0) AS exits_quantity,
                    COALESCE(SUM(quantity), 0) AS net_quantity
             FROM stock_movements
             WHERE product_id = :product_id
               AND movement_date >= :period_start
               AND movement_date < :period_end_exclusive"
        );
        $movementSummaryStatement->execute([
            'product_id' => $productId,
            'period_start' => $periodStart,
            'period_end_exclusive' => $periodEndExclusive,
        ]);
        $movementSummary = $movementSummaryStatement->fetch() ?: [];

        $stockStartAdjustmentStatement = $this->db->prepare(
            'SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE product_id = :product_id AND movement_date >= :period_start'
        );
        $stockStartAdjustmentStatement->execute([
            'product_id' => $productId,
            'period_start' => $periodStart,
        ]);
        $stockStartAdjustment = (float) $stockStartAdjustmentStatement->fetchColumn();

        $stockEndAdjustmentStatement = $this->db->prepare(
            'SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE product_id = :product_id AND movement_date >= :period_end_exclusive'
        );
        $stockEndAdjustmentStatement->execute([
            'product_id' => $productId,
            'period_end_exclusive' => $periodEndExclusive,
        ]);
        $stockEndAdjustment = (float) $stockEndAdjustmentStatement->fetchColumn();

        $productSalesStatement = $this->db->prepare(
            "SELECT i.id,
                    i.invoice_number,
                    i.invoice_date,
                    c.company_name AS client_name,
                    ii.quantity,
                    ii.unit_price,
                    ii.line_total
             FROM invoice_items ii
             INNER JOIN invoices i ON i.id = ii.invoice_id
             INNER JOIN clients c ON c.id = i.client_id
             WHERE i.deleted_at IS NULL
               AND i.status IN ('validated', 'partial_paid', 'paid')
               AND ii.item_type = 'product'
               AND ii.product_id = :product_id
               AND i.invoice_date BETWEEN :date_from AND :date_to
             ORDER BY i.invoice_date DESC, i.id DESC
             LIMIT 12"
        );
        $productSalesStatement->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $productSalesStatement->bindValue(':date_from', $dateFrom);
        $productSalesStatement->bindValue(':date_to', $dateTo);
        $productSalesStatement->execute();

        $currentStock = (float) ($selectedProduct['current_stock'] ?? 0);
        $quantitySold = (float) ($productSummary['quantity_sold'] ?? 0);
        $revenue = (float) ($productSummary['revenue'] ?? 0);

        $analysis['selected_product'] = $selectedProduct;
        $analysis['product_summary'] = [
            'invoice_count' => (int) ($productSummary['invoice_count'] ?? 0),
            'quantity_sold' => $quantitySold,
            'revenue' => $revenue,
            'average_unit_price' => $quantitySold > 0 ? ($revenue / $quantitySold) : 0,
            'entries_quantity' => (float) ($movementSummary['entries_quantity'] ?? 0),
            'exits_quantity' => (float) ($movementSummary['exits_quantity'] ?? 0),
            'net_quantity' => (float) ($movementSummary['net_quantity'] ?? 0),
            'stock_start' => $currentStock - $stockStartAdjustment,
            'stock_end' => $currentStock - $stockEndAdjustment,
            'current_stock' => $currentStock,
            'minimum_stock' => (float) ($selectedProduct['minimum_stock'] ?? 0),
            'first_sale_date' => (string) ($productSummary['first_sale_date'] ?? ''),
            'last_sale_date' => (string) ($productSummary['last_sale_date'] ?? ''),
        ];
        $analysis['product_sales'] = $productSalesStatement->fetchAll();

        return $analysis;
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

    public function adjustShopStock(int $id, int $shopId, float $newStock): void
    {
        $statement = $this->db->prepare('INSERT INTO product_stocks (product_id, shop_id, current_stock, created_at, updated_at)
            VALUES (:product_id, :shop_id, :current_stock, NOW(), NOW())
            ON DUPLICATE KEY UPDATE current_stock = VALUES(current_stock), updated_at = NOW()');
        $statement->execute([
            'product_id' => $id,
            'shop_id' => $shopId,
            'current_stock' => $newStock,
        ]);
    }
}
