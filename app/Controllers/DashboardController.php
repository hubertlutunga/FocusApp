<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Report;
use App\Models\StockMovement;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $db = Database::connection();
        $isCashierDashboard = Auth::hasRole(['caisse', 'caissier']);
        $isAdminDashboard = Auth::hasRole(['administrateur']);
        $isStockDashboard = Auth::hasRole(['gestionnaire_stock']) && !$isCashierDashboard && !$isAdminDashboard;
        $salesDateFrom = trim((string) ($_GET['sales_date_from'] ?? ''));
        $salesDateTo = trim((string) ($_GET['sales_date_to'] ?? ''));

        $stats = [
            'clients' => (int) $db->query('SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL')->fetchColumn(),
            'products' => (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL')->fetchColumn(),
            'services' => (int) $db->query('SELECT COUNT(*) FROM services WHERE deleted_at IS NULL')->fetchColumn(),
            'invoices' => (int) $db->query("SELECT COUNT(*) FROM invoices WHERE deleted_at IS NULL AND status <> 'cancelled'")->fetchColumn(),
            'stock_value' => (float) $db->query('SELECT COALESCE(SUM(current_stock * sale_price), 0) FROM products WHERE deleted_at IS NULL')->fetchColumn(),
        ];

        $salesStatement = $db->query(
            "SELECT DATE_FORMAT(invoice_date, '%Y-%m') AS period, COALESCE(SUM(grand_total), 0) AS total
             FROM invoices
             WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid')
             GROUP BY DATE_FORMAT(invoice_date, '%Y-%m')
             ORDER BY period ASC
             LIMIT 6"
        );

        $salesData = $salesStatement->fetchAll();

        $chartLabels = array_map(static fn (array $row): string => $row['period'], $salesData);
        $chartValues = array_map(static fn (array $row): float => (float) $row['total'], $salesData);

        $cashierOverview = null;
        $salesTable = [];
        $adminOverview = null;
        $adminChartData = null;
        $stockOverview = null;
        $stockChartData = null;
        $stockCriticalProducts = [];
        $stockRecentMovements = [];

        if ($isCashierDashboard) {
            $cashierOverview = [
                'total_sales' => (float) $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid')")->fetchColumn(),
                'today_sales' => (float) $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND invoice_date = CURDATE()")->fetchColumn(),
                'month_sales' => (float) $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND YEAR(invoice_date) = YEAR(CURDATE()) AND MONTH(invoice_date) = MONTH(CURDATE())")->fetchColumn(),
                'year_sales' => (float) $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND YEAR(invoice_date) = YEAR(CURDATE())")->fetchColumn(),
            ];

            $salesSql = 'SELECT i.*, c.company_name AS client_name, u.full_name AS user_name
                        FROM invoices i
                        INNER JOIN clients c ON c.id = i.client_id
                        INNER JOIN users u ON u.id = i.created_by
                        WHERE i.deleted_at IS NULL';
            $salesParams = [];

            if ($salesDateFrom !== '') {
                $salesSql .= ' AND i.invoice_date >= :sales_date_from';
                $salesParams['sales_date_from'] = $salesDateFrom;
            }

            if ($salesDateTo !== '') {
                $salesSql .= ' AND i.invoice_date <= :sales_date_to';
                $salesParams['sales_date_to'] = $salesDateTo;
            }

            $salesSql .= ' ORDER BY i.id DESC LIMIT 20';
            $salesStatement = $db->prepare($salesSql);
            $salesStatement->execute($salesParams);
            $salesTable = $salesStatement->fetchAll();
        }

        if ($isAdminDashboard) {
            $reportModel = new Report();
            $overview = $reportModel->overview();
            $comparison = $reportModel->salesVsExpenses();
            $topClients = $reportModel->topClients();
            $lowStockProducts = $reportModel->lowStockProducts();

            $salesMap = [];
            foreach ($comparison['sales'] as $row) {
                $salesMap[$row['period']] = (float) $row['total'];
            }

            $expensesMap = [];
            foreach ($comparison['expenses'] as $row) {
                $expensesMap[$row['period']] = (float) $row['total'];
            }

            $periods = array_values(array_unique(array_merge(array_keys($salesMap), array_keys($expensesMap))));
            sort($periods);

            $salesSeries = [];
            $expensesSeries = [];
            foreach ($periods as $period) {
                $salesSeries[] = $salesMap[$period] ?? 0;
                $expensesSeries[] = $expensesMap[$period] ?? 0;
            }

            $monthSales = (float) $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND YEAR(invoice_date) = YEAR(CURDATE()) AND MONTH(invoice_date) = MONTH(CURDATE())")->fetchColumn();
            $todaySales = (float) $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND invoice_date = CURDATE()")->fetchColumn();
            $monthExpenses = (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE deleted_at IS NULL AND YEAR(expense_date) = YEAR(CURDATE()) AND MONTH(expense_date) = MONTH(CURDATE())")->fetchColumn();
            $todayExpenses = (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE deleted_at IS NULL AND expense_date = CURDATE()")->fetchColumn();
            $grossProfitEstimate = $monthSales - $monthExpenses;
            $monthlyProductSales = (float) $db->query("SELECT COALESCE(SUM(ii.line_total), 0) FROM invoice_items ii INNER JOIN invoices i ON i.id = ii.invoice_id WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid') AND ii.item_type = 'product' AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND MONTH(i.invoice_date) = MONTH(CURDATE())")->fetchColumn();
            $monthlyServiceSales = (float) $db->query("SELECT COALESCE(SUM(ii.line_total), 0) FROM invoice_items ii INNER JOIN invoices i ON i.id = ii.invoice_id WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid') AND ii.item_type = 'service' AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND MONTH(i.invoice_date) = MONTH(CURDATE())")->fetchColumn();
            $monthlyClients = (int) $db->query("SELECT COUNT(DISTINCT client_id) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND YEAR(invoice_date) = YEAR(CURDATE()) AND MONTH(invoice_date) = MONTH(CURDATE())")->fetchColumn();
            $outstandingCount = (int) $db->query("SELECT COUNT(*) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid') AND balance_due > 0")->fetchColumn();
            $lowStockCount = count($lowStockProducts);
            $activeProducts = (int) $db->query("SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND is_active = 1")->fetchColumn();
            $healthyStockCount = max($activeProducts - $lowStockCount, 0);

            $paymentsRows = $db->query("SELECT DATE_FORMAT(payment_date, '%Y-%m') AS period, COALESCE(SUM(amount), 0) AS total FROM payments WHERE deleted_at IS NULL GROUP BY DATE_FORMAT(payment_date, '%Y-%m') ORDER BY period ASC LIMIT 6")->fetchAll();
            $outstandingRows = $db->query("SELECT DATE_FORMAT(invoice_date, '%Y-%m') AS period, COALESCE(SUM(balance_due), 0) AS total FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid') GROUP BY DATE_FORMAT(invoice_date, '%Y-%m') ORDER BY period ASC LIMIT 6")->fetchAll();

            $paymentsMap = [];
            foreach ($paymentsRows as $row) {
                $paymentsMap[$row['period']] = (float) $row['total'];
            }

            $outstandingMap = [];
            foreach ($outstandingRows as $row) {
                $outstandingMap[$row['period']] = (float) $row['total'];
            }

            $cashPeriods = array_values(array_unique(array_merge(array_keys($paymentsMap), array_keys($outstandingMap))));
            sort($cashPeriods);

            $paymentsSeries = [];
            $outstandingSeries = [];
            foreach ($cashPeriods as $period) {
                $paymentsSeries[] = $paymentsMap[$period] ?? 0;
                $outstandingSeries[] = $outstandingMap[$period] ?? 0;
            }

            $statusRows = $db->query("SELECT status, COUNT(*) AS total FROM invoices WHERE deleted_at IS NULL GROUP BY status ORDER BY status ASC")->fetchAll();
            $statusLabels = [];
            $statusValues = [];
            foreach ($statusRows as $row) {
                $statusLabels[] = status_label((string) $row['status']);
                $statusValues[] = (int) $row['total'];
            }

            $adminOverview = [
                'month_sales' => $monthSales,
                'today_sales' => $todaySales,
                'month_expenses' => $monthExpenses,
                'today_expenses' => $todayExpenses,
                'gross_profit_estimate' => $grossProfitEstimate,
                'monthly_product_sales' => $monthlyProductSales,
                'monthly_service_sales' => $monthlyServiceSales,
                'monthly_clients' => $monthlyClients,
                'outstanding_total' => (float) ($overview['outstanding_total'] ?? 0),
                'outstanding_count' => $outstandingCount,
                'stock_value' => (float) $stats['stock_value'],
                'low_stock_count' => $lowStockCount,
                'payments_total' => (float) ($overview['payments_total'] ?? 0),
            ];

            $adminChartData = [
                'comparison_labels' => $periods,
                'sales_series' => $salesSeries,
                'expenses_series' => $expensesSeries,
                'top_client_labels' => array_map(static fn (array $row): string => $row['company_name'], $topClients),
                'top_client_values' => array_map(static fn (array $row): float => (float) $row['total'], $topClients),
                'status_labels' => $statusLabels,
                'status_values' => $statusValues,
                'stock_labels' => ['Stock sain', 'Stock faible'],
                'stock_values' => [$healthyStockCount, $lowStockCount],
                'cash_labels' => $cashPeriods,
                'payments_series' => $paymentsSeries,
                'outstanding_series' => $outstandingSeries,
            ];
        }

        if ($isStockDashboard) {
            $inventoryCostValue = (float) $db->query('SELECT COALESCE(SUM(current_stock * cost_price), 0) FROM products WHERE deleted_at IS NULL AND is_active = 1')->fetchColumn();
            $inventorySaleValue = (float) $db->query('SELECT COALESCE(SUM(current_stock * sale_price), 0) FROM products WHERE deleted_at IS NULL AND is_active = 1')->fetchColumn();
            $activeProducts = (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND is_active = 1')->fetchColumn();
            $lowStockCount = (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock > 0 AND current_stock <= minimum_stock')->fetchColumn();
            $outOfStockCount = (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock <= 0')->fetchColumn();
            $healthyStockCount = max($activeProducts - $lowStockCount - $outOfStockCount, 0);
            $todayMovements = (int) $db->query('SELECT COUNT(*) FROM stock_movements WHERE DATE(movement_date) = CURDATE()')->fetchColumn();
            $monthlyEntries = (float) $db->query('SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE quantity > 0 AND YEAR(movement_date) = YEAR(CURDATE()) AND MONTH(movement_date) = MONTH(CURDATE())')->fetchColumn();
            $monthlyExits = (float) $db->query('SELECT COALESCE(SUM(ABS(quantity)), 0) FROM stock_movements WHERE quantity < 0 AND YEAR(movement_date) = YEAR(CURDATE()) AND MONTH(movement_date) = MONTH(CURDATE())')->fetchColumn();
            $pendingProcurements = (int) $db->query("SELECT COUNT(*) FROM procurements WHERE deleted_at IS NULL AND status IN ('draft', 'ordered')")->fetchColumn();
            $categoriesCovered = (int) $db->query('SELECT COUNT(DISTINCT category_id) FROM products WHERE deleted_at IS NULL AND is_active = 1')->fetchColumn();
            $stockCoverageRate = $activeProducts > 0 ? (($healthyStockCount / $activeProducts) * 100) : 0.0;

            $movementTrendRows = $db->query("SELECT period, entries, exits FROM (
                    SELECT DATE_FORMAT(movement_date, '%Y-%m') AS period,
                           COALESCE(SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END), 0) AS entries,
                           COALESCE(SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END), 0) AS exits
                    FROM stock_movements
                    GROUP BY DATE_FORMAT(movement_date, '%Y-%m')
                    ORDER BY period DESC
                    LIMIT 6
                ) movement_trends
                ORDER BY period ASC")->fetchAll();

            $categoryValueStatement = $db->prepare('SELECT c.name, COALESCE(SUM(p.current_stock * p.cost_price), 0) AS total
                    FROM products p
                    INNER JOIN categories c ON c.id = p.category_id
                    WHERE p.deleted_at IS NULL AND p.is_active = 1
                    GROUP BY c.id, c.name
                    ORDER BY total DESC
                    LIMIT :limit');
            $categoryValueStatement->bindValue(':limit', 6, \PDO::PARAM_INT);
            $categoryValueStatement->execute();
            $categoryValueRows = $categoryValueStatement->fetchAll();

            $criticalProductsStatement = $db->prepare('SELECT p.id, p.sku, p.name, p.current_stock, p.minimum_stock, c.name AS category_name, u.symbol AS unit_symbol
                    FROM products p
                    INNER JOIN categories c ON c.id = p.category_id
                    INNER JOIN units u ON u.id = p.unit_id
                    WHERE p.deleted_at IS NULL AND p.is_active = 1 AND p.current_stock <= p.minimum_stock
                    ORDER BY p.current_stock ASC, p.minimum_stock DESC, p.name ASC
                    LIMIT :limit');
            $criticalProductsStatement->bindValue(':limit', 6, \PDO::PARAM_INT);
            $criticalProductsStatement->execute();
            $stockCriticalProducts = $criticalProductsStatement->fetchAll();

            $recentMovementsStatement = $db->prepare('SELECT sm.movement_date, sm.movement_type, sm.quantity, sm.reference_type, sm.reference_id, p.name AS product_name, p.sku, u.full_name AS user_name
                    FROM stock_movements sm
                    INNER JOIN products p ON p.id = sm.product_id
                    LEFT JOIN users u ON u.id = sm.created_by
                    ORDER BY sm.id DESC
                    LIMIT :limit');
            $recentMovementsStatement->bindValue(':limit', 6, \PDO::PARAM_INT);
            $recentMovementsStatement->execute();
            $stockRecentMovements = $recentMovementsStatement->fetchAll();

            $stockOverview = [
                'inventory_cost_value' => $inventoryCostValue,
                'inventory_sale_value' => $inventorySaleValue,
                'active_products' => $activeProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'healthy_stock_count' => $healthyStockCount,
                'today_movements' => $todayMovements,
                'monthly_entries' => $monthlyEntries,
                'monthly_exits' => $monthlyExits,
                'pending_procurements' => $pendingProcurements,
                'categories_covered' => $categoriesCovered,
                'stock_coverage_rate' => $stockCoverageRate,
            ];

            $stockChartData = [
                'health_labels' => ['Stock sain', 'Stock faible', 'Rupture'],
                'health_values' => [$healthyStockCount, $lowStockCount, $outOfStockCount],
                'movement_labels' => array_map(static fn (array $row): string => $row['period'], $movementTrendRows),
                'movement_entries' => array_map(static fn (array $row): float => (float) $row['entries'], $movementTrendRows),
                'movement_exits' => array_map(static fn (array $row): float => (float) $row['exits'], $movementTrendRows),
                'category_labels' => array_map(static fn (array $row): string => $row['name'], $categoryValueRows),
                'category_values' => array_map(static fn (array $row): float => (float) $row['total'], $categoryValueRows),
            ];
        }

        $this->render('dashboard.index', [
            'pageTitle' => 'Tableau de bord',
            'user' => Auth::user(),
            'isAdminDashboard' => $isAdminDashboard,
            'isCashierDashboard' => $isCashierDashboard,
            'isStockDashboard' => $isStockDashboard,
            'salesDateFrom' => $salesDateFrom,
            'salesDateTo' => $salesDateTo,
            'stats' => $stats,
            'adminOverview' => $adminOverview,
            'adminChartData' => $adminChartData,
            'stockOverview' => $stockOverview,
            'stockChartData' => $stockChartData,
            'stockCriticalProducts' => $stockCriticalProducts,
            'stockRecentMovements' => $stockRecentMovements,
            'cashierOverview' => $cashierOverview,
            'salesTable' => $salesTable,
            'activities' => (new ActivityLog())->latest(10),
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }
}
