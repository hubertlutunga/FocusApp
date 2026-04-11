<div class="row g-4">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Entrée manuelle</h3>
                <p class="text-muted mb-0">Les sorties sont calculées automatiquement à partir des factures validées par la caisse.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <form method="post" action="<?= e(url('/stock/adjust')); ?>" class="row g-3">
                    <?= csrf_field(); ?>
                    <?php $selectedProduct = (int) old('product_id', '0'); ?>
                    <div class="col-12">
                        <label class="form-label" for="product_id">Produit</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= e((string) $product['id']); ?>" <?= $selectedProduct === (int) $product['id'] ? 'selected' : ''; ?>><?= e($product['name'] . ' (' . $product['sku'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="quantity">Quantité</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="quantity" name="quantity" value="<?= e(old('quantity', '')); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="note">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3"><?= e(old('note', '')); ?></textarea>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-primary">Enregistrer le mouvement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Alertes stock minimum</h3>
                <p class="text-muted mb-0">Surveillez les références proches de la rupture.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($lowStockProducts === []): ?>
                    <div class="alert alert-success mb-0">Aucune alerte de stock bas pour le moment.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr><th>SKU</th><th>Produit</th><th>Stock</th><th>Minimum</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td><?= e($product['sku']); ?></td>
                                        <td><?= e($product['name']); ?></td>
                                        <td class="text-danger fw-semibold"><?= e(number_format((float) $product['current_stock'], 2, ',', ' ')); ?></td>
                                        <td><?= e(number_format((float) $product['minimum_stock'], 2, ',', ' ')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Historique des mouvements</h3>
                <p class="text-muted mb-0">Entrées, sorties issues des factures, réceptions et mouvements manuels autorisés.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-striped align-middle js-datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Produit</th>
                                <th data-mobile-hidden="true">Type</th>
                                <th>Quantité</th>
                                <th data-mobile-hidden="true">Avant</th>
                                <th data-mobile-hidden="true">Après</th>
                                <th data-mobile-hidden="true">Référence</th>
                                <th data-mobile-hidden="true">Auteur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td><?= e(date('d/m/Y H:i', strtotime((string) $movement['movement_date']))); ?></td>
                                    <td><?= e($movement['product_name'] . ' (' . $movement['sku'] . ')'); ?></td>
                                    <td><?= e($movement['movement_type']); ?></td>
                                    <td class="<?= (float) $movement['quantity'] < 0 ? 'text-danger' : 'text-success'; ?> fw-semibold"><?= e(number_format((float) $movement['quantity'], 2, ',', ' ')); ?></td>
                                    <td><?= e(number_format((float) $movement['quantity_before'], 2, ',', ' ')); ?></td>
                                    <td><?= e(number_format((float) $movement['quantity_after'], 2, ',', ' ')); ?></td>
                                    <td><?= e(($movement['reference_type'] ?? '—') . ($movement['reference_id'] ? ' #' . $movement['reference_id'] : '')); ?></td>
                                    <td><?= e($movement['user_name'] ?: 'Système'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
