<?php
$productCount = count($products);
$totalStock = array_reduce($products, static fn (float $carry, array $product): float => $carry + (float) $product['current_stock'], 0.0);
$lowStockCount = count(array_filter($products, static fn (array $product): bool => (float) $product['current_stock'] <= (float) $product['minimum_stock']));
$isShopStock = $currentShopId !== null;
?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1"><?= $isShopStock ? 'Stock boutique' : 'Stock général'; ?></h1>
        <p class="text-muted mb-0"><?= $isShopStock ? 'Stock disponible dans ' . e($currentShopName ?: 'votre boutique') . '.' : 'Consultez le stock central et transférez des produits vers les boutiques.'; ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (!$isShopStock): ?>
            <a href="<?= e(url('/procurements')); ?>" class="btn btn-primary">
                <i class="bi bi-cart-plus me-1"></i> Approvisionnement
            </a>
        <?php endif; ?>
    </div>
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

<?php if (!$isShopStock): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h3 class="h5 mb-1">Transférer vers une boutique</h3>
        <p class="text-muted mb-0">Recherchez rapidement les produits et transférez plusieurs lignes en une seule opération.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e(url('/stock/transfer')); ?>" class="row g-3 align-items-end stock-multi-form" data-row-template="transferRowTemplate" data-row-container="transferRows">
            <?= csrf_field(); ?>
            <div class="col-md-3">
                <label class="form-label" for="destination_shop_id">Boutique</label>
                <select class="form-select" id="destination_shop_id" name="destination_shop_id" required>
                    <option value="">Destination</option>
                    <?php foreach ($shops as $shop): ?>
                        <option value="<?= e((string) $shop['id']); ?>"><?= e($shop['name'] . ' (' . $shop['code'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label" for="transfer_note">Note</label>
                <input class="form-control" id="transfer_note" name="note" placeholder="Motif du transfert">
            </div>
            <div class="col-12">
                <div class="stock-transfer-rows" id="transferRows">
                    <div class="row g-2 align-items-end stock-transfer-row">
                        <div class="col-md-7">
                            <label class="form-label">Produit</label>
                            <input type="search" class="form-control form-control-sm mb-1 js-select-search" placeholder="Rechercher produit, SKU..." data-target-select=".js-product-select">
                            <select class="form-select js-product-select" name="items[product_id][]" required>
                                <option value="">Sélectionner</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= e((string) $product['id']); ?>"><?= e($product['name'] . ' (' . $product['sku'] . ') — Stock : ' . number_format((float) $product['current_stock'], 2, ',', ' ')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="items[quantity][]" required>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" class="btn btn-outline-danger js-remove-stock-row" disabled>Retirer</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary js-add-stock-row">Ajouter un autre produit</button>
                <button type="submit" class="btn btn-primary">Transférer le stock</button>
            </div>
        </form>
    </div>
</div>
<template id="transferRowTemplate">
    <div class="row g-2 align-items-end stock-transfer-row mt-2">
        <div class="col-md-7">
            <label class="form-label">Produit</label>
            <input type="search" class="form-control form-control-sm mb-1 js-select-search" placeholder="Rechercher produit, SKU..." data-target-select=".js-product-select">
            <select class="form-select js-product-select" name="items[product_id][]" required>
                <option value="">Sélectionner</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= e((string) $product['id']); ?>"><?= e($product['name'] . ' (' . $product['sku'] . ') — Stock : ' . number_format((float) $product['current_stock'], 2, ',', ' ')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantité</label>
            <input type="number" step="0.01" min="0.01" class="form-control" name="items[quantity][]" required>
        </div>
        <div class="col-md-2 d-grid">
            <button type="button" class="btn btn-outline-danger js-remove-stock-row">Retirer</button>
        </div>
    </div>
</template>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h3 class="h5 mb-1">Retourner au stock général</h3>
        <p class="text-muted mb-0">Sélectionnez un ou plusieurs produits de votre extension à retourner au stock général.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e(url('/stock/return')); ?>" class="row g-3 align-items-end stock-multi-form" data-row-template="returnRowTemplate" data-row-container="returnRows">
            <?= csrf_field(); ?>
            <div class="col-12">
                <label class="form-label" for="return_note">Note</label>
                <input class="form-control" id="return_note" name="note" placeholder="Motif du retour">
            </div>
            <div class="col-12">
                <div class="stock-transfer-rows" id="returnRows">
                    <div class="row g-2 align-items-end stock-transfer-row">
                        <div class="col-md-7">
                            <label class="form-label">Produit</label>
                            <input type="search" class="form-control form-control-sm mb-1 js-select-search" placeholder="Rechercher produit, SKU..." data-target-select=".js-product-select">
                            <select class="form-select js-product-select" name="items[product_id][]" required>
                                <option value="">Sélectionner</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= e((string) $product['id']); ?>"><?= e($product['name'] . ' (' . $product['sku'] . ') — Stock boutique : ' . number_format((float) $product['current_stock'], 2, ',', ' ')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="items[quantity][]" required>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" class="btn btn-outline-danger js-remove-stock-row" disabled>Retirer</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary js-add-stock-row">Ajouter un autre produit</button>
                <button type="submit" class="btn btn-primary">Retourner le stock</button>
            </div>
        </form>
    </div>
</div>
<template id="returnRowTemplate">
    <div class="row g-2 align-items-end stock-transfer-row mt-2">
        <div class="col-md-7">
            <label class="form-label">Produit</label>
            <input type="search" class="form-control form-control-sm mb-1 js-select-search" placeholder="Rechercher produit, SKU..." data-target-select=".js-product-select">
            <select class="form-select js-product-select" name="items[product_id][]" required>
                <option value="">Sélectionner</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= e((string) $product['id']); ?>"><?= e($product['name'] . ' (' . $product['sku'] . ') — Stock boutique : ' . number_format((float) $product['current_stock'], 2, ',', ' ')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantité</label>
            <input type="number" step="0.01" min="0.01" class="form-control" name="items[quantity][]" required>
        </div>
        <div class="col-md-2 d-grid">
            <button type="button" class="btn btn-outline-danger js-remove-stock-row">Retirer</button>
        </div>
    </div>
</template>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Produits et stock disponible</h3>
            <p class="text-muted mb-0"><?= $isShopStock ? 'Vue synthétique du stock de cette boutique.' : 'Vue synthétique du stock général actuel par produit.'; ?></p>
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
                        <?php if ($isShopStock): ?><th data-mobile-hidden="true">Stock général</th><?php endif; ?>
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
                            <?php if ($isShopStock): ?><td><?= e(number_format((float) ($product['central_stock'] ?? 0), 2, ',', ' ')); ?></td><?php endif; ?>
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

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h3 class="h5 mb-1">Derniers transferts / retours</h3>
        <p class="text-muted mb-0"><?= $isShopStock ? 'Transferts reçus et retours effectués par cette boutique.' : 'Transferts récents du stock général et retours des extensions.'; ?></p>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if ($recentTransfers === []): ?>
            <div class="alert alert-light mb-0">Aucun transfert enregistré.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle js-datatable">
                    <thead><tr><th>Produit</th><th>Mouvement</th><th>Quantité</th><th data-mobile-hidden="true">Date</th><th data-mobile-hidden="true">Agent</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentTransfers as $transfer): ?>
                            <?php $isReturn = ($transfer['transfer_type'] ?? 'to_shop') === 'to_central'; ?>
                            <tr>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($transfer['product_name']); ?></div>
                                        <div class="table-cell-meta"><?= e($transfer['sku']); ?><?= $transfer['note'] ? ' • ' . e($transfer['note']) : ''; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= $isReturn ? 'Retour vers stock général' : 'Transfert vers boutique'; ?></div>
                                        <div class="table-cell-meta"><?= e($isReturn ? ($transfer['source_shop_name'] ?: 'Boutique') : ($transfer['destination_shop_name'] ?: 'Boutique')); ?></div>
                                    </div>
                                </td>
                                <td class="fw-semibold <?= $isReturn ? 'text-warning' : 'text-success'; ?>">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= e(movement_tooltip($isReturn ? 'transfer_out' : 'transfer_in')); ?>">
                                        <?= e(number_format((float) $transfer['quantity'], 2, ',', ' ')); ?>
                                        <i class="bi bi-info-circle ms-1"></i>
                                    </span>
                                </td>
                                <td><?= e(date('d/m/Y H:i', strtotime((string) $transfer['created_at']))); ?></td>
                                <td><?= e($transfer['user_name'] ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
