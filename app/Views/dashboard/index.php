<?php if (!empty($isCashierDashboard)): ?>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-cash-stack"></i></span>
                <div>
                    <p class="text-muted mb-1">Ventes totales</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($cashierOverview['total_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon success"><i class="bi bi-calendar-day"></i></span>
                <div>
                    <p class="text-muted mb-1">Aujourd’hui</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($cashierOverview['today_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon warning"><i class="bi bi-calendar-month"></i></span>
                <div>
                    <p class="text-muted mb-1">Ce mois</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($cashierOverview['month_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon danger"><i class="bi bi-calendar3"></i></span>
                <div>
                    <p class="text-muted mb-1">Cette année</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($cashierOverview['year_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Tableau des ventes</h3>
            <p class="text-muted mb-0">Dernières ventes et statuts de facturation.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a href="<?= e(url('/invoices/create')); ?>" class="btn btn-primary">Nouvelle vente</a>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="get" action="<?= e(url('/dashboard')); ?>" class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label" for="salesQuickSearch">Recherche rapide</label>
                <input type="search" id="salesQuickSearch" class="form-control" placeholder="Facture, client, statut..." data-datatable-target="#cashierSalesTable">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="sales_date_from">Du</label>
                <input type="date" id="sales_date_from" name="sales_date_from" class="form-control" value="<?= e((string) ($salesDateFrom ?? '')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="sales_date_to">Au</label>
                <input type="date" id="sales_date_to" name="sales_date_to" class="form-control" value="<?= e((string) ($salesDateTo ?? '')); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-fill">Filtrer</button>
                <a href="<?= e(url('/dashboard')); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table id="cashierSalesTable" class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Facture</th>
                        <th>Statut</th>
                        <th>Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesTable as $invoice): ?>
                        <tr data-invoice-row-id="<?= e((string) $invoice['id']); ?>">
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($invoice['invoice_number']); ?></div>
                                    <div class="table-cell-meta"><?= e($invoice['client_name']); ?></div>
                                    <div class="table-cell-meta"><?= e(date('d/m/Y', strtotime((string) $invoice['invoice_date']))); ?></div>
                                </div>
                            </td>
                            <td><span class="badge <?= e(status_badge_class($invoice['status'])); ?>" data-role="invoice-status-badge"><?= e(status_label($invoice['status'])); ?></span></td>
                            <td>
                                <div class="table-cell-stack" data-role="invoice-amounts">
                                    <div class="table-cell-main text-amount"><?= e(number_format((float) $invoice['grand_total'], 2, ',', ' ')); ?></div>
                                    <div class="table-cell-meta" data-role="invoice-paid-label">Payé : <?= e(number_format((float) $invoice['amount_paid'], 2, ',', ' ')); ?></div>
                                    <div class="table-cell-meta" data-role="invoice-balance-label">Solde : <?= e(number_format((float) $invoice['balance_due'], 2, ',', ' ')); ?></div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="table-actions" data-role="invoice-actions">
                                    <a href="<?= e(url('/invoices/show?id=' . $invoice['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Voir</a>
                                    <?php if (in_array($invoice['status'], ['validated', 'partial_paid'], true) && (float) $invoice['balance_due'] > 0): ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-success table-action-btn js-open-payment-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cashierPaymentModal"
                                            data-invoice-id="<?= e((string) $invoice['id']); ?>"
                                            data-invoice-number="<?= e($invoice['invoice_number']); ?>"
                                            data-client-name="<?= e($invoice['client_name']); ?>"
                                            data-balance-due="<?= e(number_format((float) $invoice['balance_due'], 2, '.', '')); ?>"
                                            data-balance-label="<?= e(number_format((float) $invoice['balance_due'], 2, ',', ' ')); ?>">
                                            Encaisser
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= e(url('/invoices/pdf?id=' . $invoice['id'])); ?>" target="_blank" class="btn btn-sm btn-outline-secondary table-action-btn">PDF</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="cashierPaymentModal" tabindex="-1" aria-labelledby="cashierPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 mb-1" id="cashierPaymentModalLabel">Encaisser une vente</h2>
                    <p class="text-muted mb-0 small">Enregistrer un paiement sans quitter le tableau de bord.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="document-party-card mb-3">
                    <div class="document-section-label mb-2">Facture sélectionnée</div>
                    <div class="document-meta-list">
                        <span id="cashierPaymentInvoiceNumber">—</span>
                        <span id="cashierPaymentClientName">—</span>
                        <span>Solde restant : <strong id="cashierPaymentBalanceLabel">0,00</strong></span>
                    </div>
                </div>

                <form method="post" action="<?= e(url('/payments/store')); ?>" class="row g-3" id="cashierPaymentForm">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="invoice_id" id="cashierPaymentInvoiceId" value="">
                    <input type="hidden" name="redirect_to" value="/dashboard">

                    <div class="col-md-6">
                        <label class="form-label" for="cashier_payment_date">Date paiement</label>
                        <input type="date" class="form-control" id="cashier_payment_date" name="payment_date" value="<?= e(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cashier_payment_amount">Montant</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="cashier_payment_amount" name="amount" value="" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="cashier_payment_method">Méthode</label>
                        <select class="form-select" id="cashier_payment_method" name="method">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Virement</option>
                            <option value="card">Carte</option>
                            <option value="cheque">Chèque</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="cashier_payment_reference">Référence</label>
                        <input class="form-control" id="cashier_payment_reference" name="reference">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="cashier_payment_notes">Note</label>
                        <textarea class="form-control" id="cashier_payment_notes" name="notes" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-danger d-none mb-0" id="cashierPaymentError"></div>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary" id="cashierPaymentSubmit">Enregistrer le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="h5 mb-1">Évolution des ventes</h3>
                        <p class="text-muted mb-0">Suivi des factures validées dans le temps.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="salesChart"
                                data-labels='<?= e(json_encode($chartLabels, JSON_UNESCAPED_UNICODE)); ?>'
                                data-values='<?= e(json_encode($chartValues, JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif (!empty($isStockDashboard)): ?>
        <?php
        $stockCoverageRate = (float) ($stockOverview['stock_coverage_rate'] ?? 0);
        $stockCoverageState = $stockCoverageRate < 50 ? 'critical' : ($stockCoverageRate < 75 ? 'alert' : 'healthy');
        $stockCoverageLabel = $stockCoverageState === 'critical' ? 'Couverture faible' : ($stockCoverageState === 'alert' ? 'Couverture à surveiller' : 'Couverture maîtrisée');
        $stockCoverageIcon = $stockCoverageState === 'critical' ? 'danger' : ($stockCoverageState === 'alert' ? 'warning' : 'success');
        ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Pilotage du stock</h2>
                <p class="text-muted mb-0">Vue opérationnelle des niveaux, mouvements et alertes de réapprovisionnement.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= e(url('/stock')); ?>" class="btn btn-outline-primary">Mouvements</a>
                <a href="<?= e(url('/products')); ?>" class="btn btn-outline-secondary">Produits</a>
                <a href="<?= e(url('/procurements')); ?>" class="btn btn-primary">Approvisionnements</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100 admin-metric-card stock-metric-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon primary"><i class="bi bi-boxes"></i></span>
                        <div>
                            <p class="text-muted mb-1">Valeur stock</p>
                            <h3 class="mb-0 text-amount"><?= e(number_format((float) ($stockOverview['inventory_cost_value'] ?? 0), 2, ',', ' ')); ?></h3>
                            <small class="metric-subnote">Prix coûtant · Vente estimée : <?= e(number_format((float) ($stockOverview['inventory_sale_value'] ?? 0), 2, ',', ' ')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100 admin-metric-card stock-metric-card stock-metric-card-alert">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon warning"><i class="bi bi-exclamation-triangle"></i></span>
                        <div>
                            <p class="text-muted mb-1">Produits en alerte</p>
                            <h3 class="mb-0"><?= e((string) ($stockOverview['low_stock_count'] ?? 0)); ?></h3>
                            <small class="metric-subnote">Ruptures : <?= e((string) ($stockOverview['out_of_stock_count'] ?? 0)); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100 admin-metric-card stock-metric-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon success"><i class="bi bi-arrow-down-circle"></i></span>
                        <div>
                            <p class="text-muted mb-1">Entrées du mois</p>
                            <h3 class="mb-0"><?= e(number_format((float) ($stockOverview['monthly_entries'] ?? 0), 2, ',', ' ')); ?></h3>
                            <small class="metric-subnote">Appro en attente : <?= e((string) ($stockOverview['pending_procurements'] ?? 0)); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100 admin-metric-card stock-metric-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon danger"><i class="bi bi-arrow-up-circle"></i></span>
                        <div>
                            <p class="text-muted mb-1">Sorties du mois</p>
                            <h3 class="mb-0"><?= e(number_format((float) ($stockOverview['monthly_exits'] ?? 0), 2, ',', ' ')); ?></h3>
                            <small class="metric-subnote">Mouvements aujourd’hui : <?= e((string) ($stockOverview['today_movements'] ?? 0)); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100 admin-metric-card stock-metric-card stock-metric-card-coverage is-<?= e($stockCoverageState); ?>">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon <?= e($stockCoverageIcon); ?>"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <p class="text-muted mb-1">Couverture stock</p>
                            <h3 class="mb-0 <?= $stockCoverageState === 'critical' ? 'text-danger' : ($stockCoverageState === 'alert' ? 'text-warning' : 'text-success'); ?>"><?= e(number_format($stockCoverageRate, 0, ',', ' ')); ?>%</h3>
                            <small class="metric-subnote <?= $stockCoverageState === 'critical' ? 'text-danger' : ($stockCoverageState === 'alert' ? 'text-warning' : 'text-success'); ?>"><?= e($stockCoverageLabel); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100 admin-metric-card stock-metric-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon primary"><i class="bi bi-grid-1x2"></i></span>
                        <div>
                            <p class="text-muted mb-1">Base stock active</p>
                            <h3 class="mb-0"><?= e((string) ($stockOverview['active_products'] ?? 0)); ?></h3>
                            <small class="metric-subnote"><?= e((string) ($stockOverview['categories_covered'] ?? 0)); ?> catégorie(s) couvertes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="h5 mb-1">Flux des mouvements</h3>
                        <p class="text-muted mb-0">Entrées et sorties de stock sur les derniers mois.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="chart-wrap admin-chart-wrap">
                            <canvas id="stockMovementChart"
                                    data-labels='<?= e(json_encode($stockChartData['movement_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                                    data-entries='<?= e(json_encode($stockChartData['movement_entries'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                                    data-exits='<?= e(json_encode($stockChartData['movement_exits'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="h5 mb-1">Santé du stock</h3>
                        <p class="text-muted mb-0">Répartition des produits sains, faibles et en rupture.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="chart-wrap admin-chart-wrap admin-chart-wrap-sm">
                            <canvas id="stockHealthManagerChart"
                                    data-labels='<?= e(json_encode($stockChartData['health_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                                    data-values='<?= e(json_encode($stockChartData['health_values'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="h5 mb-1">Valeur du stock par catégorie</h3>
                        <p class="text-muted mb-0">Catégories qui portent le plus de valeur immobilisée.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="chart-wrap admin-chart-wrap">
                            <canvas id="stockCategoryValueChart"
                                    data-labels='<?= e(json_encode($stockChartData['category_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                                    data-values='<?= e(json_encode($stockChartData['category_values'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 stock-list-card">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="h5 mb-1">Produits critiques</h3>
                        <p class="text-muted mb-0">Articles sous seuil minimum ou déjà en rupture.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <?php if (!empty($stockCriticalProducts)): ?>
                            <div class="stock-critical-list">
                                <?php foreach ($stockCriticalProducts as $product): ?>
                                    <?php $isOut = (float) $product['current_stock'] <= 0; ?>
                                    <div class="stock-critical-item d-flex justify-content-between align-items-start gap-3">
                                        <div class="min-w-0">
                                            <div class="fw-semibold"><?= e($product['name']); ?></div>
                                            <div class="table-cell-meta"><?= e($product['sku']); ?> · <?= e($product['category_name']); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold <?= $isOut ? 'text-danger' : 'text-warning'; ?>"><?= e(number_format((float) $product['current_stock'], 2, ',', ' ')); ?> <?= e($product['unit_symbol']); ?></div>
                                            <div class="table-cell-meta">Min : <?= e(number_format((float) $product['minimum_stock'], 2, ',', ' ')); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-check2-circle"></i>
                                <div>Aucun produit critique pour le moment.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm stock-list-card">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h3 class="h5 mb-1">Derniers mouvements</h3>
                    <p class="text-muted mb-0">Trace récente des entrées, sorties et ajustements.</p>
                </div>
                <a href="<?= e(url('/stock')); ?>" class="btn btn-sm btn-outline-primary">Voir tout l’historique</a>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if (!empty($stockRecentMovements)): ?>
                    <div class="stock-movement-list">
                        <?php foreach ($stockRecentMovements as $movement): ?>
                            <?php $isExit = (float) $movement['quantity'] < 0; ?>
                            <div class="stock-movement-item d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-start gap-3 min-w-0">
                                    <span class="stock-movement-icon <?= $isExit ? 'is-exit' : 'is-entry'; ?>">
                                        <i class="bi <?= $isExit ? 'bi-arrow-up-right' : 'bi-arrow-down-left'; ?>"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold"><?= e($movement['product_name']); ?> <span class="text-muted">· <?= e($movement['sku']); ?></span></div>
                                        <div class="stock-movement-meta">
                                            <span><?= e((string) ($movement['user_name'] ?: 'Système')); ?></span>
                                            <span>•</span>
                                            <span><?= e((string) ($movement['reference_type'] ?: 'manuel')); ?><?= !empty($movement['reference_id']) ? ' #' . e((string) $movement['reference_id']) : ''; ?></span>
                                            <span>•</span>
                                            <span><?= e(date('d/m/Y H:i', strtotime((string) $movement['movement_date']))); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold <?= $isExit ? 'text-danger' : 'text-success'; ?>"><?= e(number_format(abs((float) $movement['quantity']), 2, ',', ' ')); ?></div>
                                    <div class="table-cell-meta"><?= e(movement_label($movement['movement_type'] ?? 'manual')); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-box-seam"></i>
                        <div>Aucun mouvement de stock enregistré récemment.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const parseDataset = function (value) {
        try {
            return JSON.parse(value || '[]');
        } catch (error) {
            return [];
        }
    };

    const movementCanvas = document.getElementById('stockMovementChart');
    if (movementCanvas) {
        new Chart(movementCanvas, {
            type: 'bar',
            data: {
                labels: parseDataset(movementCanvas.dataset.labels),
                datasets: [
                    {
                        label: 'Entrées',
                        data: parseDataset(movementCanvas.dataset.entries),
                        backgroundColor: 'rgba(32, 201, 151, 0.8)',
                        borderRadius: 10,
                        maxBarThickness: 34
                    },
                    {
                        label: 'Sorties',
                        data: parseDataset(movementCanvas.dataset.exits),
                        backgroundColor: 'rgba(220, 53, 69, 0.75)',
                        borderRadius: 10,
                        maxBarThickness: 34
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
    }

    const healthCanvas = document.getElementById('stockHealthManagerChart');
    if (healthCanvas) {
        new Chart(healthCanvas, {
            type: 'doughnut',
            data: {
                labels: parseDataset(healthCanvas.dataset.labels),
                datasets: [{
                    data: parseDataset(healthCanvas.dataset.values),
                    backgroundColor: ['#20c997', '#f59e0b', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const categoryCanvas = document.getElementById('stockCategoryValueChart');
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: 'bar',
            data: {
                labels: parseDataset(categoryCanvas.dataset.labels),
                datasets: [{
                    label: 'Valeur',
                    data: parseDataset(categoryCanvas.dataset.values),
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderRadius: 10,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
<?php elseif (!empty($isAdminDashboard)): ?>
<?php
$grossProfitEstimate = (float) ($adminOverview['gross_profit_estimate'] ?? 0);
$grossProfitPositive = $grossProfitEstimate >= 0;
$outstandingTotal = (float) ($adminOverview['outstanding_total'] ?? 0);
$monthSalesAmount = max((float) ($adminOverview['month_sales'] ?? 0), 1);
$debtRatio = $outstandingTotal / $monthSalesAmount;
$debtState = $debtRatio >= 0.6 ? 'critical' : ($debtRatio >= 0.3 ? 'alert' : 'normal');
$debtStateLabel = $debtState === 'critical' ? 'Dette élevée' : ($debtState === 'alert' ? 'Dette à surveiller' : 'Dette maîtrisée');
$debtIconClass = $debtState === 'critical' ? 'danger' : ($debtState === 'alert' ? 'warning' : 'success');
?>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-graph-up-arrow"></i></span>
                <div>
                    <p class="text-muted mb-1">Ventes</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['month_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote">Jour : <?= e(number_format((float) ($adminOverview['today_sales'] ?? 0), 2, ',', ' ')); ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon danger"><i class="bi bi-wallet2"></i></span>
                <div>
                    <p class="text-muted mb-1">Dépenses</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['month_expenses'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote">Jour : <?= e(number_format((float) ($adminOverview['today_expenses'] ?? 0), 2, ',', ' ')); ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-tools"></i></span>
                <div>
                    <p class="text-muted mb-1">Vente service</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['monthly_service_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote">Cumul mensuel services</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon warning"><i class="bi bi-receipt-cutoff"></i></span>
                <div>
                    <p class="text-muted mb-1">Vente produit</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['monthly_product_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote">Cumul mensuel produits</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon success"><i class="bi bi-box-seam"></i></span>
                <div>
                    <p class="text-muted mb-1">Stock valorisé</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['stock_value'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote"><?= e((string) ($adminOverview['low_stock_count'] ?? 0)); ?> produit(s) en alerte</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card admin-metric-card-profit <?= $grossProfitPositive ? 'is-positive' : 'is-negative'; ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon <?= $grossProfitPositive ? 'success' : 'danger'; ?>"><i class="bi <?= $grossProfitPositive ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow'; ?>"></i></span>
                <div>
                    <p class="text-muted mb-1">Bénéfice brut estimé</p>
                    <h3 class="mb-0 text-amount <?= $grossProfitPositive ? 'text-success' : 'text-danger'; ?>"><?= e(number_format($grossProfitEstimate, 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote <?= $grossProfitPositive ? 'text-success' : 'text-danger'; ?>">
                        <i class="bi <?= $grossProfitPositive ? 'bi-arrow-up-right' : 'bi-arrow-down-right'; ?> me-1"></i>
                        <?= $grossProfitPositive ? 'Marge positive ce mois' : 'Marge sous pression ce mois'; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card admin-metric-card-debt is-<?= e($debtState); ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon <?= e($debtIconClass); ?>"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <p class="text-muted mb-1">Dette client</p>
                    <h3 class="mb-0 text-amount <?= $debtState === 'critical' ? 'text-danger' : ($debtState === 'alert' ? 'text-warning' : 'text-success'); ?>"><?= e(number_format($outstandingTotal, 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote <?= $debtState === 'critical' ? 'text-danger' : ($debtState === 'alert' ? 'text-warning' : 'text-success'); ?>"><?= e($debtStateLabel); ?> · <?= e((string) ($adminOverview['outstanding_count'] ?? 0)); ?> facture(s)</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-bank2"></i></span>
                <div>
                    <p class="text-muted mb-1">Dette Focus</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['focus_debt_total'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote">Solde fournisseurs et charges à régler</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card metric-card h-100 admin-metric-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon warning"><i class="bi bi-receipt-cutoff"></i></span>
                <div>
                    <p class="text-muted mb-1">Tax (TVA)</p>
                    <h3 class="mb-0 text-amount"><?= e(number_format((float) ($adminOverview['monthly_tax_collected'] ?? 0), 2, ',', ' ')); ?></h3>
                    <small class="metric-subnote">Total mensuel collecte sur factures</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Ventes vs dépenses</h3>
                <p class="text-muted mb-0">Comparaison mensuelle des flux commerciaux et des charges.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="chart-wrap admin-chart-wrap">
                    <canvas id="adminSalesExpensesChart"
                            data-labels='<?= e(json_encode($adminChartData['comparison_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-sales='<?= e(json_encode($adminChartData['sales_series'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-expenses='<?= e(json_encode($adminChartData['expenses_series'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Répartition des statuts</h3>
                <p class="text-muted mb-0">Vision du portefeuille de factures.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="chart-wrap admin-chart-wrap admin-chart-wrap-sm">
                    <canvas id="adminInvoiceStatusChart"
                            data-labels='<?= e(json_encode($adminChartData['status_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-values='<?= e(json_encode($adminChartData['status_values'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Top clients</h3>
                <p class="text-muted mb-0">Clients les plus contributeurs au chiffre d’affaires.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="chart-wrap admin-chart-wrap">
                    <canvas id="adminTopClientsChart"
                            data-labels='<?= e(json_encode($adminChartData['top_client_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-values='<?= e(json_encode($adminChartData['top_client_values'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Encaissements vs dettes</h3>
                <p class="text-muted mb-0">Suivi de la capacité d’encaissement face aux créances clients.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="chart-wrap admin-chart-wrap">
                    <canvas id="adminCashFlowChart"
                            data-labels='<?= e(json_encode($adminChartData['cash_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-payments='<?= e(json_encode($adminChartData['payments_series'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-outstanding='<?= e(json_encode($adminChartData['outstanding_series'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Santé du stock</h3>
                <p class="text-muted mb-0">Produits sains contre produits sous seuil.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="chart-wrap admin-chart-wrap">
                    <canvas id="adminStockHealthChart"
                            data-labels='<?= e(json_encode($adminChartData['stock_labels'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'
                            data-values='<?= e(json_encode($adminChartData['stock_values'] ?? [], JSON_UNESCAPED_UNICODE)); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 admin-activity-card">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h3 class="h5 mb-1">Journal d’activité</h3>
                    <p class="text-muted mb-0">Dernières actions sensibles enregistrées dans l’application.</p>
                </div>
                <span class="admin-activity-chip">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Live
                </span>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="list-group list-group-flush small admin-activity-list">
                    <?php foreach (array_slice($activities ?? [], 0, 6) as $activity): ?>
                        <div class="list-group-item px-0 admin-activity-item d-flex justify-content-between align-items-start gap-3">
                            <div class="d-flex align-items-start gap-3 min-w-0">
                                <span class="admin-activity-dot"></span>
                                <div class="min-w-0">
                                    <div class="fw-semibold admin-activity-title"><?= e($activity['description']); ?></div>
                                    <div class="admin-activity-meta">
                                        <span><?= e($activity['full_name'] ?? 'Système'); ?></span>
                                        <span class="admin-activity-separator">•</span>
                                        <span class="admin-activity-module"><?= e(module_label($activity['module'] ?? 'system')); ?></span>
                                    </div>
                                </div>
                            </div>
                            <small class="admin-activity-time text-nowrap"><?= e(date('d/m H:i', strtotime((string) $activity['created_at']))); ?></small>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($activities)): ?>
                        <div class="admin-activity-empty">
                            <i class="bi bi-clock-history"></i>
                            <span>Aucune activité récente.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const parseDataset = function (value) {
        try {
            return JSON.parse(value || '[]');
        } catch (error) {
            return [];
        }
    };

    const salesExpensesCanvas = document.getElementById('adminSalesExpensesChart');
    if (salesExpensesCanvas) {
        new Chart(salesExpensesCanvas, {
            type: 'line',
            data: {
                labels: parseDataset(salesExpensesCanvas.dataset.labels),
                datasets: [
                    {
                        label: 'Ventes',
                        data: parseDataset(salesExpensesCanvas.dataset.sales),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2
                    },
                    {
                        label: 'Dépenses',
                        data: parseDataset(salesExpensesCanvas.dataset.expenses),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2
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
    }

    const statusCanvas = document.getElementById('adminInvoiceStatusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: parseDataset(statusCanvas.dataset.labels),
                datasets: [{
                    data: parseDataset(statusCanvas.dataset.values),
                    backgroundColor: ['#0d6efd', '#20c997', '#f59e0b', '#8b5cf6', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const topClientsCanvas = document.getElementById('adminTopClientsChart');
    if (topClientsCanvas) {
        new Chart(topClientsCanvas, {
            type: 'bar',
            data: {
                labels: parseDataset(topClientsCanvas.dataset.labels),
                datasets: [{
                    label: 'Montant',
                    data: parseDataset(topClientsCanvas.dataset.values),
                    backgroundColor: 'rgba(13, 110, 253, 0.78)',
                    borderRadius: 10,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    const stockCanvas = document.getElementById('adminStockHealthChart');
    if (stockCanvas) {
        new Chart(stockCanvas, {
            type: 'doughnut',
            data: {
                labels: parseDataset(stockCanvas.dataset.labels),
                datasets: [{
                    data: parseDataset(stockCanvas.dataset.values),
                    backgroundColor: ['#20c997', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const cashFlowCanvas = document.getElementById('adminCashFlowChart');
    if (cashFlowCanvas) {
        new Chart(cashFlowCanvas, {
            type: 'bar',
            data: {
                labels: parseDataset(cashFlowCanvas.dataset.labels),
                datasets: [
                    {
                        label: 'Encaissements',
                        data: parseDataset(cashFlowCanvas.dataset.payments),
                        backgroundColor: 'rgba(32, 201, 151, 0.78)',
                        borderRadius: 10,
                        maxBarThickness: 30
                    },
                    {
                        label: 'Dettes',
                        data: parseDataset(cashFlowCanvas.dataset.outstanding),
                        backgroundColor: 'rgba(245, 158, 11, 0.78)',
                        borderRadius: 10,
                        maxBarThickness: 30
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
    }
});
</script>
<?php else: ?>
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card stat-card-clients border-0 h-100">
            <div class="card-body stat-card-body stat-card-body-clients">
                <div>
                    <div class="stat-card-label">Clients</div>
                    <div class="display-6 fw-bold mb-1"><?= e((string) $stats['clients']); ?></div>
                    <div class="stat-card-hint">Base clients active</div>
                </div>
                <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card stat-card-products border-0 h-100">
            <div class="card-body stat-card-body stat-card-body-products">
                <div>
                    <div class="stat-card-label">Produits</div>
                    <div class="display-6 fw-bold mb-1"><?= e((string) $stats['products']); ?></div>
                    <div class="stat-card-hint">Catalogue physique</div>
                </div>
                <div class="stat-card-icon"><i class="bi bi-box-seam-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card stat-card-services border-0 h-100">
            <div class="card-body stat-card-body stat-card-body-services">
                <div>
                    <div class="stat-card-label">Services</div>
                    <div class="display-6 fw-bold mb-1"><?= e((string) $stats['services']); ?></div>
                    <div class="stat-card-hint">Prestations disponibles</div>
                </div>
                <div class="stat-card-icon"><i class="bi bi-tools"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card stat-card-stock border-0 h-100">
            <div class="card-body stat-card-body stat-card-body-stock">
                <div>
                    <div class="stat-card-label">Valeur du stock</div>
                    <div class="display-6 fw-bold mb-1 stat-card-value stat-card-value-stock"><?= e(number_format((float) $stats['stock_value'], 2, ',', ' ')); ?></div>
                    <div class="stat-card-hint">Capital immobilisé</div>
                </div>
                <div class="stat-card-icon"><i class="bi bi-cash-coin"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Évolution des factures validées</h3>
                <p class="text-muted mb-0">Suivi des factures validées dans le temps.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <canvas id="salesChart"
                        data-labels='<?= e(json_encode($chartLabels, JSON_UNESCAPED_UNICODE)); ?>'
                        data-values='<?= e(json_encode($chartValues, JSON_UNESCAPED_UNICODE)); ?>'></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
