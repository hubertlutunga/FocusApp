<?php $netResult = ((float) ($overview['validated_sales'] ?? 0)) - ((float) ($overview['total_expenses'] ?? 0)); ?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1">Rapports</h1>
        <p class="text-muted mb-0">Analysez les performances commerciales et financières.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-graph-up-arrow"></i></span>
                <div>
                    <p class="text-muted mb-1">Ventes validées</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($overview['validated_sales'] ?? 0), 2, ',', ' ')) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon success"><i class="bi bi-cash-coin"></i></span>
                <div>
                    <p class="text-muted mb-1">Encaissements</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($overview['payments_received'] ?? 0), 2, ',', ' ')) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon danger"><i class="bi bi-wallet2"></i></span>
                <div>
                    <p class="text-muted mb-1">Dépenses</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($overview['total_expenses'] ?? 0), 2, ',', ' ')) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon warning"><i class="bi bi-truck"></i></span>
                <div>
                    <p class="text-muted mb-1">Approvisionnements reçus</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($overview['received_procurements'] ?? 0), 2, ',', ' ')) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card soft-card mb-4">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="muted-label mb-1">Résultat brut estimé</div>
            <div class="h4 mb-0 <?= $netResult >= 0 ? 'text-success' : 'text-danger' ?> text-amount"><?= e(number_format($netResult, 2, ',', ' ')) ?></div>
        </div>
        <span class="summary-chip"><i class="bi bi-info-circle"></i> Calculé sur ventes validées moins dépenses enregistrées</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h2 class="h5 mb-0">Ventes vs dépenses</h2>
            </div>
            <div class="card-body">
                <?php if ($periods === []): ?>
                    <div class="empty-state">
                        <i class="bi bi-bar-chart-line"></i>
                        <div class="fw-semibold mb-1">Pas encore assez de données</div>
                        <p class="mb-0">Le graphique s’alimentera dès que des factures et dépenses seront enregistrées.</p>
                    </div>
                <?php else: ?>
                    <div class="chart-wrap">
                        <canvas id="salesExpensesChart" height="120"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h2 class="h5 mb-0">Top clients</h2>
            </div>
            <div class="card-body">
                <?php if ($topClients === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-people"></i>
                        <div class="fw-semibold mb-1">Aucun client classé</div>
                        <p class="mb-0">Les meilleurs clients apparaîtront après validation des factures.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($topClients as $client): ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?= e($client['client_name']) ?></div>
                                    <small class="text-muted"><?= e((string) $client['invoice_count']) ?> facture(s)</small>
                                </div>
                                <span class="fw-semibold text-amount"><?= e(number_format((float) $client['total_amount'], 2, ',', ' ')) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h2 class="h5 mb-0">Produits en stock faible</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Référence</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Seuil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($lowStockProducts === []): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Aucun produit sous le seuil.</td></tr>
                            <?php else: ?>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td><?= e($product['name']) ?></td>
                                        <td><?= e($product['reference']) ?></td>
                                        <td class="text-end text-danger fw-semibold text-amount"><?= e(number_format((float) $product['stock_quantity'], 2, ',', ' ')) ?></td>
                                        <td class="text-end text-amount"><?= e(number_format((float) $product['alert_threshold'], 2, ',', ' ')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h2 class="h5 mb-0">Dernières dépenses</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Libellé</th>
                                <th>Catégorie</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentExpenses === []): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Aucune dépense récente.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentExpenses as $expense): ?>
                                    <tr>
                                        <td><?= e(date('d/m/Y', strtotime($expense['expense_date']))) ?></td>
                                        <td><?= e($expense['description']) ?></td>
                                        <td><?= e($expense['category_name']) ?></td>
                                        <td class="text-end fw-semibold text-amount"><?= e(number_format((float) $expense['amount'], 2, ',', ' ')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('salesExpensesChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: <?= json_encode($periods, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [
                {
                    label: 'Ventes',
                    data: <?= json_encode($salesSeries, JSON_UNESCAPED_UNICODE) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.12)',
                    fill: true,
                    tension: 0.35
                },
                {
                    label: 'Dépenses',
                    data: <?= json_encode($expensesSeries, JSON_UNESCAPED_UNICODE) ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.08)',
                    fill: true,
                    tension: 0.35
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
