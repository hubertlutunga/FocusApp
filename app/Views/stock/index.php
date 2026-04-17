<?php
$productCount = count($products);
$totalStock = array_reduce($products, static fn (float $carry, array $product): float => $carry + (float) $product['current_stock'], 0.0);
$lowStockCount = count(array_filter($products, static fn (array $product): bool => (float) $product['current_stock'] <= (float) $product['minimum_stock']));
?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1">Stock disponible</h1>
        <p class="text-muted mb-0">Consultez rapidement les produits en stock et lancez un approvisionnement si nécessaire.</p>
    </div>
    <a href="<?= e(url('/procurements')); ?>" class="btn btn-primary">
        <i class="bi bi-cart-plus me-1"></i> Approvisionnement
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-box-seam"></i></span>
                <div>
                    <div class="muted-label">Produits suivis</div>
                    <div class="h4 mb-0"><?= e((string) $productCount); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon success"><i class="bi bi-boxes"></i></span>
                <div>
                    <div class="muted-label">Stock total</div>
                    <div class="h4 mb-0 text-amount"><?= e(number_format($totalStock, 2, ',', ' ')); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon danger"><i class="bi bi-exclamation-triangle"></i></span>
                <div>
                    <div class="muted-label">Stock faible</div>
                    <div class="h4 mb-0"><?= e((string) $lowStockCount); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Produits et stock disponible</h3>
            <p class="text-muted mb-0">Vue synthétique du stock actuel par produit.</p>
        </div>
        <span class="muted-label"><?= e((string) $productCount); ?> produit(s)</span>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th data-mobile-hidden="true">Catégorie</th>
                        <th data-mobile-hidden="true">Unité</th>
                        <th>Stock disponible</th>
                        <th data-mobile-hidden="true">Seuil min.</th>
                        <th data-mobile-hidden="true">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php $isLowStock = (float) $product['current_stock'] <= (float) $product['minimum_stock']; ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($product['name']); ?></div>
                                    <div class="table-cell-meta"><?= e($product['sku']); ?></div>
                                </div>
                            </td>
                            <td><?= e($product['category_name']); ?></td>
                            <td><?= e($product['unit_symbol'] ?: $product['unit_name']); ?></td>
                            <td class="fw-semibold <?= $isLowStock ? 'text-danger' : 'text-success'; ?>">
                                <?= e(number_format((float) $product['current_stock'], 2, ',', ' ')); ?>
                            </td>
                            <td><?= e(number_format((float) $product['minimum_stock'], 2, ',', ' ')); ?></td>
                            <td>
                                <span class="badge rounded-pill <?= $isLowStock ? 'text-bg-danger' : 'text-bg-success'; ?>">
                                    <?= $isLowStock ? 'À réapprovisionner' : 'Disponible'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
