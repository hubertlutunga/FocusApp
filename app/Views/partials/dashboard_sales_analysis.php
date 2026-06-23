<?php
$analysis = $salesAnalysis ?? [];
$periodSummary = $analysis['period_summary'] ?? [];
$topProducts = $analysis['top_products'] ?? [];
$selectedProduct = $analysis['selected_product'] ?? null;
$productSummary = $analysis['product_summary'] ?? null;
$productSales = $analysis['product_sales'] ?? [];
$selectedProductMissing = !empty($analysisProductId) && $selectedProduct === null;
?>

<div class="card border-0 shadow-sm mt-4 mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h3 class="h5 mb-1">Analyse des ventes par période</h3>
                <p class="text-muted mb-0">Choisissez une période et, si besoin, un produit précis pour suivre les ventes, le stock et les mouvements associés.</p>
            </div>
            <a href="<?= e(url('/dashboard')); ?>" class="btn btn-outline-secondary">Reinitialiser</a>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="get" action="<?= e(url('/dashboard')); ?>" class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label class="form-label" for="analysis_date_from">Du</label>
                <input type="date" id="analysis_date_from" name="analysis_date_from" class="form-control" value="<?= e((string) ($analysisDateFrom ?? '')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="analysis_date_to">Au</label>
                <input type="date" id="analysis_date_to" name="analysis_date_to" class="form-control" value="<?= e((string) ($analysisDateTo ?? '')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="analysis_product_id">Produit</label>
                <select id="analysis_product_id" name="analysis_product_id" class="form-select">
                    <option value="">Tous les produits</option>
                    <?php foreach (($analysisProductOptions ?? []) as $productOption): ?>
                        <option value="<?= e((string) $productOption['id']); ?>" <?= (int) ($analysisProductId ?? 0) === (int) $productOption['id'] ? 'selected' : ''; ?>><?= e($productOption['name'] . ' (' . $productOption['sku'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Consulter</button>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon primary"><i class="bi bi-calendar-range"></i></span>
                        <div>
                            <p class="text-muted mb-1">CA sur la periode</p>
                            <h3 class="mb-0 text-amount"><?= e(number_format((float) ($periodSummary['total_sales'] ?? 0), 2, ',', ' ')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon success"><i class="bi bi-receipt"></i></span>
                        <div>
                            <p class="text-muted mb-1">Factures validees</p>
                            <h3 class="mb-0"><?= e((string) ((int) ($periodSummary['invoice_count'] ?? 0))); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon warning"><i class="bi bi-box-seam"></i></span>
                        <div>
                            <p class="text-muted mb-1">Quantites vendues</p>
                            <h3 class="mb-0"><?= e(number_format((float) ($periodSummary['product_quantity_sold'] ?? 0), 2, ',', ' ')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="metric-icon danger"><i class="bi bi-cash-coin"></i></span>
                        <div>
                            <p class="text-muted mb-1">CA produits</p>
                            <h3 class="mb-0 text-amount"><?= e(number_format((float) ($periodSummary['product_sales_amount'] ?? 0), 2, ',', ' ')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card h-100 border-light-subtle">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="h6 mb-1">Produits les plus vendus</h4>
                        <p class="text-muted mb-0">Classement sur la periode selectionnee.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <?php if ($topProducts !== []): ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th class="text-end">Qté</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topProducts as $topProduct): ?>
                                            <tr>
                                                <td>
                                                    <div class="table-cell-stack">
                                                        <div class="table-cell-main"><?= e($topProduct['name']); ?></div>
                                                        <div class="table-cell-meta"><?= e($topProduct['sku']); ?></div>
                                                    </div>
                                                </td>
                                                <td class="text-end"><?= e(number_format((float) $topProduct['quantity_sold'], 2, ',', ' ')); ?> <?= e($topProduct['unit_symbol']); ?></td>
                                                <td class="text-end text-amount"><?= e(number_format((float) $topProduct['revenue'], 2, ',', ' ')); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-bar-chart-line"></i>
                                <div>Aucune vente validee sur cette periode.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100 border-light-subtle">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="h6 mb-1">Focus produit</h4>
                        <p class="text-muted mb-0">Selectionnez un produit pour voir combien a ete vendu, le stock au debut et a la fin de la periode, ainsi que l'etat actuel.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <?php if ($selectedProductMissing): ?>
                            <div class="alert alert-warning mb-0">Le produit selectionne est introuvable ou archive.</div>
                        <?php elseif ($selectedProduct && $productSummary): ?>
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <div class="fw-semibold fs-5"><?= e($selectedProduct['name']); ?></div>
                                    <div class="table-cell-meta"><?= e($selectedProduct['sku']); ?> · <?= e($selectedProduct['category_name']); ?> · Stock mini : <?= e(number_format((float) ($productSummary['minimum_stock'] ?? 0), 2, ',', ' ')); ?> <?= e($selectedProduct['unit_symbol']); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">Derniere vente sur la periode</div>
                                    <div class="fw-semibold"><?= !empty($productSummary['last_sale_date']) ? e(date('d/m/Y', strtotime((string) $productSummary['last_sale_date']))) : 'Aucune'; ?></div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-light-subtle">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Quantite vendue</div>
                                            <div class="fs-4 fw-semibold"><?= e(number_format((float) ($productSummary['quantity_sold'] ?? 0), 2, ',', ' ')); ?> <?= e($selectedProduct['unit_symbol']); ?></div>
                                            <div class="table-cell-meta"><?= e((string) ((int) ($productSummary['invoice_count'] ?? 0))); ?> facture(s)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-light-subtle">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Montant vendu</div>
                                            <div class="fs-4 fw-semibold text-amount"><?= e(number_format((float) ($productSummary['revenue'] ?? 0), 2, ',', ' ')); ?></div>
                                            <div class="table-cell-meta">Prix moyen : <?= e(number_format((float) ($productSummary['average_unit_price'] ?? 0), 2, ',', ' ')); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-light-subtle">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Stock actuel</div>
                                            <div class="fs-4 fw-semibold <?= (float) ($productSummary['current_stock'] ?? 0) <= (float) ($productSummary['minimum_stock'] ?? 0) ? 'text-danger' : ''; ?>"><?= e(number_format((float) ($productSummary['current_stock'] ?? 0), 2, ',', ' ')); ?> <?= e($selectedProduct['unit_symbol']); ?></div>
                                            <div class="table-cell-meta">Prix vente : <?= e(number_format((float) ($selectedProduct['sale_price'] ?? 0), 2, ',', ' ')); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-light-subtle">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Stock debut periode</div>
                                            <div class="fs-4 fw-semibold"><?= e(number_format((float) ($productSummary['stock_start'] ?? 0), 2, ',', ' ')); ?> <?= e($selectedProduct['unit_symbol']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-light-subtle">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Stock fin periode</div>
                                            <div class="fs-4 fw-semibold"><?= e(number_format((float) ($productSummary['stock_end'] ?? 0), 2, ',', ' ')); ?> <?= e($selectedProduct['unit_symbol']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-light-subtle">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Mouvements periode</div>
                                            <div class="fw-semibold text-success">Entrées : <?= e(number_format((float) ($productSummary['entries_quantity'] ?? 0), 2, ',', ' ')); ?></div>
                                            <div class="fw-semibold text-danger">Sorties : <?= e(number_format((float) ($productSummary['exits_quantity'] ?? 0), 2, ',', ' ')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($productSales !== []): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Facture</th>
                                                <th>Client</th>
                                                <th class="text-end">Qté</th>
                                                <th class="text-end">P.U.</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($productSales as $sale): ?>
                                                <tr>
                                                    <td>
                                                        <div class="table-cell-stack">
                                                            <div class="table-cell-main"><?= e($sale['invoice_number']); ?></div>
                                                            <div class="table-cell-meta"><?= e(date('d/m/Y', strtotime((string) $sale['invoice_date']))); ?></div>
                                                        </div>
                                                    </td>
                                                    <td><?= e($sale['client_name']); ?></td>
                                                    <td class="text-end"><?= e(number_format((float) $sale['quantity'], 2, ',', ' ')); ?></td>
                                                    <td class="text-end"><?= e(number_format((float) $sale['unit_price'], 2, ',', ' ')); ?></td>
                                                    <td class="text-end text-amount"><?= e(number_format((float) $sale['line_total'], 2, ',', ' ')); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-box-seam"></i>
                                    <div>Aucune vente validee pour ce produit sur la periode selectionnee.</div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-search"></i>
                                <div>Selectionnez un produit pour consulter son detail sur la periode.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>