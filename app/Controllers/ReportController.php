<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;

final class ReportController extends Controller
{
    public function index(): void
    {
        $reportModel = new Report();
        $comparison = $reportModel->salesVsExpenses();

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

        $this->render('reports.index', [
            'pageTitle' => 'Rapports',
            'overview' => $reportModel->overview(),
            'periods' => $periods,
            'salesSeries' => $salesSeries,
            'expensesSeries' => $expensesSeries,
            'topClients' => $reportModel->topClients(),
            'lowStockProducts' => $reportModel->lowStockProducts(),
            'recentExpenses' => $reportModel->recentExpenses(),
        ]);
    }
}
