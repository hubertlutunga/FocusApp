<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Report extends Model
{
    public function overview(): array
    {
        return [
            'sales_total' => (float) $this->db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid')")->fetchColumn(),
            'payments_total' => (float) $this->db->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE deleted_at IS NULL')->fetchColumn(),
            'expenses_total' => (float) $this->db->query('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE deleted_at IS NULL')->fetchColumn(),
            'procurements_total' => (float) $this->db->query("SELECT COALESCE(SUM(grand_total), 0) FROM procurements WHERE deleted_at IS NULL AND status <> 'cancelled'")->fetchColumn(),
            'outstanding_total' => (float) $this->db->query("SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid')")->fetchColumn(),
        ];
    }

    public function salesVsExpenses(): array
    {
        $sales = $this->db->query("SELECT DATE_FORMAT(invoice_date, '%Y-%m') AS period, COALESCE(SUM(grand_total), 0) AS total FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') GROUP BY DATE_FORMAT(invoice_date, '%Y-%m') ORDER BY period ASC LIMIT 6")->fetchAll();
        $expenses = $this->db->query("SELECT DATE_FORMAT(expense_date, '%Y-%m') AS period, COALESCE(SUM(amount), 0) AS total FROM expenses WHERE deleted_at IS NULL GROUP BY DATE_FORMAT(expense_date, '%Y-%m') ORDER BY period ASC LIMIT 6")->fetchAll();

        return [
            'sales' => $sales,
            'expenses' => $expenses,
        ];
    }

    public function topClients(int $limit = 5): array
    {
        $statement = $this->db->prepare("SELECT c.company_name, COALESCE(SUM(i.grand_total), 0) AS total
            FROM invoices i
            INNER JOIN clients c ON c.id = i.client_id
            WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid')
            GROUP BY c.id, c.company_name
            ORDER BY total DESC
            LIMIT :limit");
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function lowStockProducts(): array
    {
        return $this->db->query('SELECT sku, name, current_stock, minimum_stock FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock <= minimum_stock ORDER BY current_stock ASC')->fetchAll();
    }

    public function recentExpenses(int $limit = 10): array
    {
        $statement = $this->db->prepare('SELECT e.expense_number, e.expense_date, e.description, e.amount, ec.name AS category_name FROM expenses e INNER JOIN expense_categories ec ON ec.id = e.expense_category_id WHERE e.deleted_at IS NULL ORDER BY e.id DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
