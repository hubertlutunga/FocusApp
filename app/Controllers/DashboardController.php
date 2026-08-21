<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Report;
use App\Models\Shop;
use App\Models\StarlinkSubscription;
use App\Models\StockMovement;
use PDOException;

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
        $analysisDateFrom = $this->normalizeFilterDate((string) ($_GET['analysis_date_from'] ?? ''), date('Y-m-01'));
        $analysisDateTo = $this->normalizeFilterDate((string) ($_GET['analysis_date_to'] ?? ''), date('Y-m-d'));
        $analysisProductId = $this->normalizeFilterProductId($_GET['analysis_product_id'] ?? null);
        $shopOptions = (new Shop())->options(false);
        $currentShopId = current_user_shop_id();
        $dashboardShopFilter = $this->normalizeShopFilter($_GET['dashboard_shop'] ?? '', $shopOptions);

        if ($isCashierDashboard && !$isAdminDashboard && $currentShopId !== null) {
            $dashboardShopFilter = (string) $currentShopId;
        }

        if ($analysisDateFrom > $analysisDateTo) {
            [$analysisDateFrom, $analysisDateTo] = [$analysisDateTo, $analysisDateFrom];
        }

        $productModel = new Product();
        $salesAnalysis = $productModel->salesAnalysis($analysisDateFrom, $analysisDateTo, $analysisProductId);

        $stats = [
            'clients' => (int) $db->query('SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL')->fetchColumn(),
            'products' => (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL')->fetchColumn(),
            'services' => (int) $db->query('SELECT COUNT(*) FROM services WHERE deleted_at IS NULL')->fetchColumn(),
            'invoices' => (int) $db->query("SELECT COUNT(*) FROM invoices WHERE deleted_at IS NULL AND status <> 'cancelled'")->fetchColumn(),
            'stock_value' => (float) $db->query('SELECT COALESCE(SUM(current_stock * sale_price), 0) FROM products WHERE deleted_at IS NULL')->fetchColumn(),
        ];

           $chartSql = "SELECT DATE_FORMAT(i.invoice_date, '%Y-%m') AS period, COALESCE(SUM(i.grand_total), 0) AS total
               FROM invoices i
               WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid')";
           $chartParams = [];
           $this->appendInvoiceShopFilter($chartSql, $chartParams, $dashboardShopFilter, 'i');
           $chartSql .= " GROUP BY DATE_FORMAT(i.invoice_date, '%Y-%m') ORDER BY period ASC LIMIT 6";
           $salesStatement = $db->prepare($chartSql);
           $salesStatement->execute($chartParams);

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
        $dashboardShopOverview = $this->shopDashboardOverview($dashboardShopFilter);
        $dashboardShopSalesRows = $this->shopSalesRows($dashboardShopFilter, $salesDateFrom, $salesDateTo);
        $dashboardShopStockRows = $this->shopStockRows($dashboardShopFilter);
        $dashboardShopLabel = $this->shopFilterLabel($dashboardShopFilter, $shopOptions);
        $starlinkOverview = null;
        $starlinkAlerts = [];

        if (user_can_access_caisse() || user_is_admin()) {
            $starlinkModel = new StarlinkSubscription();
            $starlinkOverview = $starlinkModel->dashboardOverview();
            $starlinkAlerts = $starlinkModel->dashboardAlerts(8);
        }

        if ($isCashierDashboard) {
            $cashierOverview = $this->cashierOverview($dashboardShopFilter);

            $salesSql = 'SELECT i.*, c.company_name AS client_name, u.full_name AS user_name, s.name AS shop_name
                        FROM invoices i
                        INNER JOIN clients c ON c.id = i.client_id
                        INNER JOIN users u ON u.id = i.created_by
                        LEFT JOIN shops s ON s.id = i.shop_id
                        WHERE i.deleted_at IS NULL';
            $salesParams = [];
            $this->appendInvoiceShopFilter($salesSql, $salesParams, $dashboardShopFilter, 'i');

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
            $monthlyTaxCollected = (float) $db->query("SELECT COALESCE(SUM(tax_amount), 0) FROM invoices WHERE deleted_at IS NULL AND status IN ('validated', 'partial_paid', 'paid') AND YEAR(invoice_date) = YEAR(CURDATE()) AND MONTH(invoice_date) = MONTH(CURDATE())")->fetchColumn();
            $grossProfitEstimate = $monthSales - $monthExpenses;
            $monthlyProductSales = (float) $db->query("SELECT COALESCE(SUM(ii.line_total), 0) FROM invoice_items ii INNER JOIN invoices i ON i.id = ii.invoice_id WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid') AND ii.item_type = 'product' AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND MONTH(i.invoice_date) = MONTH(CURDATE())")->fetchColumn();
            $monthlyServiceSales = (float) $db->query("SELECT COALESCE(SUM(ii.line_total), 0) FROM invoice_items ii INNER JOIN invoices i ON i.id = ii.invoice_id WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid') AND ii.item_type = 'service' AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND MONTH(i.invoice_date) = MONTH(CURDATE())")->fetchColumn();
            try {
                $supplierDebt = (float) $db->query("SELECT COALESCE(SUM(balance_due), 0) FROM procurements WHERE deleted_at IS NULL AND payment_status IN ('unpaid', 'partial_paid')")->fetchColumn();
            } catch (PDOException) {
                $supplierDebt = 0.0;
            }

            try {
                $expenseDebt = (float) $db->query("SELECT COALESCE(SUM(balance_due), 0) FROM expenses WHERE deleted_at IS NULL AND payment_status IN ('unpaid', 'partial_paid')")->fetchColumn();
            } catch (PDOException) {
                $expenseDebt = 0.0;
            }
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
                'monthly_tax_collected' => $monthlyTaxCollected,
                'gross_profit_estimate' => $grossProfitEstimate,
                'monthly_product_sales' => $monthlyProductSales,
                'monthly_service_sales' => $monthlyServiceSales,
                'focus_debt_total' => $supplierDebt + $expenseDebt,
                'outstanding_total' => (float) ($overview['outstanding_total'] ?? 0),
                'outstanding_count' => $outstandingCount,
                'stock_value' => (float) $stats['stock_value'],
                'low_stock_count' => $lowStockCount,
                'payments_total' => (float) ($overview['payments_received'] ?? 0),
            ];

            $adminChartData = [
                'comparison_labels' => $periods,
                'sales_series' => $salesSeries,
                'expenses_series' => $expensesSeries,
                'top_client_labels' => array_map(static fn (array $row): string => (string) $row['client_name'], $topClients),
                'top_client_values' => array_map(static fn (array $row): float => (float) $row['total_amount'], $topClients),
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
            'analysisDateFrom' => $analysisDateFrom,
            'analysisDateTo' => $analysisDateTo,
            'analysisProductId' => $analysisProductId,
            'analysisProductOptions' => $productModel->options(),
            'salesAnalysis' => $salesAnalysis,
            'salesDateFrom' => $salesDateFrom,
            'salesDateTo' => $salesDateTo,
            'shopOptions' => $shopOptions,
            'dashboardShopFilter' => $dashboardShopFilter,
            'dashboardShopLabel' => $dashboardShopLabel,
            'dashboardShopOverview' => $dashboardShopOverview,
            'dashboardShopSalesRows' => $dashboardShopSalesRows,
            'dashboardShopStockRows' => $dashboardShopStockRows,
            'starlinkOverview' => $starlinkOverview,
            'starlinkAlerts' => $starlinkAlerts,
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

    private function normalizeFilterDate(string $value, string $fallback): string
    {
        if ($value === '') {
            return $fallback;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
    }

    private function normalizeFilterProductId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $productId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $productId === false ? null : (int) $productId;
    }

    private function normalizeShopFilter(mixed $value, array $shopOptions): string
    {
        $filter = trim((string) $value);

        if ($filter === '' || $filter === 'all') {
            return '';
        }

        if ($filter === 'central') {
            return 'central';
        }

        $shopId = filter_var($filter, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($shopId === false) {
            return '';
        }

        foreach ($shopOptions as $shop) {
            if ((int) $shop['id'] === (int) $shopId) {
                return (string) $shopId;
            }
        }

        return '';
    }

    private function appendInvoiceShopFilter(string &$sql, array &$params, string $shopFilter, string $alias = 'i'): void
    {
        if ($shopFilter === '') {
            return;
        }

        if ($shopFilter === 'central') {
            $sql .= ' AND ' . $alias . '.shop_id IS NULL';
            return;
        }

        $sql .= ' AND ' . $alias . '.shop_id = :dashboard_shop_id';
        $params['dashboard_shop_id'] = (int) $shopFilter;
    }

    private function cashierOverview(string $shopFilter): array
    {
        $db = Database::connection();
        $baseSql = "FROM invoices i WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid')";
        $params = [];
        $this->appendInvoiceShopFilter($baseSql, $params, $shopFilter, 'i');

        return [
            'total_sales' => $this->sumInvoiceAmount('i.grand_total', $baseSql, $params),
            'today_sales' => $this->sumInvoiceAmount('i.grand_total', $baseSql . ' AND i.invoice_date = CURDATE()', $params),
            'month_sales' => $this->sumInvoiceAmount('i.grand_total', $baseSql . ' AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND MONTH(i.invoice_date) = MONTH(CURDATE())', $params),
            'year_sales' => $this->sumInvoiceAmount('i.grand_total', $baseSql . ' AND YEAR(i.invoice_date) = YEAR(CURDATE())', $params),
        ];
    }

    private function sumInvoiceAmount(string $field, string $fromSql, array $params): float
    {
        $statement = Database::connection()->prepare('SELECT COALESCE(SUM(' . $field . '), 0) ' . $fromSql);
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    private function shopDashboardOverview(string $shopFilter): array
    {
        $db = Database::connection();
        $invoiceSql = "FROM invoices i WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid', 'paid')";
        $invoiceParams = [];
        $this->appendInvoiceShopFilter($invoiceSql, $invoiceParams, $shopFilter, 'i');

        $invoiceCountStatement = $db->prepare('SELECT COUNT(*) ' . $invoiceSql);
        $invoiceCountStatement->execute($invoiceParams);

        $outstandingSql = "FROM invoices i WHERE i.deleted_at IS NULL AND i.status IN ('validated', 'partial_paid') AND i.balance_due > 0";
        $outstandingParams = [];
        $this->appendInvoiceShopFilter($outstandingSql, $outstandingParams, $shopFilter, 'i');
        $outstandingStatement = $db->prepare('SELECT COALESCE(SUM(i.balance_due), 0) ' . $outstandingSql);
        $outstandingStatement->execute($outstandingParams);

        return [
            'total_sales' => $this->sumInvoiceAmount('i.grand_total', $invoiceSql, $invoiceParams),
            'today_sales' => $this->sumInvoiceAmount('i.grand_total', $invoiceSql . ' AND i.invoice_date = CURDATE()', $invoiceParams),
            'month_sales' => $this->sumInvoiceAmount('i.grand_total', $invoiceSql . ' AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND MONTH(i.invoice_date) = MONTH(CURDATE())', $invoiceParams),
            'invoice_count' => (int) $invoiceCountStatement->fetchColumn(),
            'outstanding_total' => (float) $outstandingStatement->fetchColumn(),
            'stock_value' => $this->stockValueForFilter($shopFilter),
            'available_products' => $this->availableProductsForFilter($shopFilter),
        ];
    }

    private function stockValueForFilter(string $shopFilter): float
    {
        $db = Database::connection();

        if ($shopFilter === 'central') {
            return (float) $db->query('SELECT COALESCE(SUM(current_stock * sale_price), 0) FROM products WHERE deleted_at IS NULL AND is_active = 1')->fetchColumn();
        }

        if ($shopFilter !== '') {
            $statement = $db->prepare('SELECT COALESCE(SUM(ps.current_stock * p.sale_price), 0)
                FROM product_stocks ps
                INNER JOIN products p ON p.id = ps.product_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1 AND ps.shop_id = :shop_id');
            $statement->execute(['shop_id' => (int) $shopFilter]);

            return (float) $statement->fetchColumn();
        }

        $centralValue = (float) $db->query('SELECT COALESCE(SUM(current_stock * sale_price), 0) FROM products WHERE deleted_at IS NULL AND is_active = 1')->fetchColumn();
        $shopsValue = (float) $db->query('SELECT COALESCE(SUM(ps.current_stock * p.sale_price), 0)
            FROM product_stocks ps
            INNER JOIN products p ON p.id = ps.product_id
            WHERE p.deleted_at IS NULL AND p.is_active = 1')->fetchColumn();

        return $centralValue + $shopsValue;
    }

    private function availableProductsForFilter(string $shopFilter): int
    {
        $db = Database::connection();

        if ($shopFilter === 'central') {
            return (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock > 0')->fetchColumn();
        }

        if ($shopFilter !== '') {
            $statement = $db->prepare('SELECT COUNT(*)
                FROM product_stocks ps
                INNER JOIN products p ON p.id = ps.product_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1 AND ps.shop_id = :shop_id AND ps.current_stock > 0');
            $statement->execute(['shop_id' => (int) $shopFilter]);

            return (int) $statement->fetchColumn();
        }

        $centralProducts = (int) $db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND is_active = 1 AND current_stock > 0')->fetchColumn();
        $shopProducts = (int) $db->query('SELECT COUNT(*)
            FROM product_stocks ps
            INNER JOIN products p ON p.id = ps.product_id
            WHERE p.deleted_at IS NULL AND p.is_active = 1 AND ps.current_stock > 0')->fetchColumn();

        return $centralProducts + $shopProducts;
    }

    private function shopSalesRows(string $shopFilter, string $dateFrom, string $dateTo): array
    {
        $sql = 'SELECT i.id, i.invoice_number, i.invoice_date, i.status, i.currency_code, i.grand_total, i.amount_paid, i.balance_due,
                    c.company_name AS client_name, COALESCE(s.name, \'Stock général / siège\') AS shop_name
                FROM invoices i
                INNER JOIN clients c ON c.id = i.client_id
                LEFT JOIN shops s ON s.id = i.shop_id
                WHERE i.deleted_at IS NULL';
        $params = [];
        $this->appendInvoiceShopFilter($sql, $params, $shopFilter, 'i');

        if ($dateFrom !== '') {
            $sql .= ' AND i.invoice_date >= :shop_sales_date_from';
            $params['shop_sales_date_from'] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= ' AND i.invoice_date <= :shop_sales_date_to';
            $params['shop_sales_date_to'] = $dateTo;
        }

        $sql .= ' ORDER BY i.id DESC LIMIT 15';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function shopStockRows(string $shopFilter): array
    {
        $db = Database::connection();

        if ($shopFilter === 'central') {
            return $db->query("SELECT p.id, p.sku, p.name, p.sale_price, p.current_stock, p.minimum_stock, u.symbol AS unit_symbol, 'Stock général / siège' AS location_name
                FROM products p
                INNER JOIN units u ON u.id = p.unit_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1
                ORDER BY p.current_stock DESC, p.name ASC
                LIMIT 20")->fetchAll();
        }

        if ($shopFilter !== '') {
            $statement = $db->prepare('SELECT p.id, p.sku, p.name, p.sale_price, COALESCE(ps.current_stock, 0) AS current_stock, COALESCE(ps.minimum_stock, p.minimum_stock) AS minimum_stock, u.symbol AS unit_symbol, s.name AS location_name
                FROM products p
                INNER JOIN units u ON u.id = p.unit_id
                INNER JOIN shops s ON s.id = :shop_id
                LEFT JOIN product_stocks ps ON ps.product_id = p.id AND ps.shop_id = :shop_stock_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1
                ORDER BY current_stock DESC, p.name ASC
                LIMIT 20');
            $statement->execute([
                'shop_id' => (int) $shopFilter,
                'shop_stock_id' => (int) $shopFilter,
            ]);

            return $statement->fetchAll();
        }

        return $db->query("SELECT * FROM (
                SELECT p.id, p.sku, p.name, p.sale_price, p.current_stock, p.minimum_stock, u.symbol AS unit_symbol, 'Stock général / siège' AS location_name
                FROM products p
                INNER JOIN units u ON u.id = p.unit_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1
                UNION ALL
                SELECT p.id, p.sku, p.name, p.sale_price, ps.current_stock, COALESCE(ps.minimum_stock, p.minimum_stock) AS minimum_stock, u.symbol AS unit_symbol, s.name AS location_name
                FROM product_stocks ps
                INNER JOIN products p ON p.id = ps.product_id
                INNER JOIN units u ON u.id = p.unit_id
                INNER JOIN shops s ON s.id = ps.shop_id
                WHERE p.deleted_at IS NULL AND p.is_active = 1
            ) stock_rows
            ORDER BY location_name ASC, current_stock DESC, name ASC
            LIMIT 30")->fetchAll();
    }

    private function shopFilterLabel(string $shopFilter, array $shopOptions): string
    {
        if ($shopFilter === '') {
            return 'Toutes les boutiques';
        }

        if ($shopFilter === 'central') {
            return 'Stock général / siège';
        }

        foreach ($shopOptions as $shop) {
            if ((int) $shop['id'] === (int) $shopFilter) {
                return (string) $shop['name'];
            }
        }

        return 'Toutes les boutiques';
    }
}
