<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="h5 mb-1">Catalogue produits</h3>
                    <p class="text-muted mb-0">Consommables informatiques et équipements stockés.</p>
                </div>
                <a href="<?= e(url('/products/create')); ?>" class="btn btn-primary">Nouveau produit</a>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-striped align-middle js-datatable">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Stock</th>
                                <th data-mobile-hidden="true">Prix vente</th>
                                <th data-mobile-hidden="true">Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="table-cell-stack">
                                            <div class="table-cell-main"><?= e($product['name']); ?></div>
                                            <div class="table-cell-meta"><?= e($product['sku']); ?> • <?= e($product['category_name']); ?><?= $product['unit_symbol'] ? ' • ' . e($product['unit_symbol']) : ''; ?></div>
                                            <div class="table-cell-meta"><?= e($product['barcode'] ?: 'Sans code-barres'); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold <?= (float) $product['current_stock'] <= (float) $product['minimum_stock'] ? 'text-danger' : ''; ?>">
                                            <?= e(number_format((float) $product['current_stock'], 2, ',', ' ')); ?>
                                        </span>
                                    </td>
                                    <td><?= e(number_format((float) $product['sale_price'], 2, ',', ' ')); ?></td>
                                    <td><span class="badge rounded-pill <?= (int) $product['is_active'] === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?= (int) $product['is_active'] === 1 ? 'Actif' : 'Inactif'; ?></span></td>
                                    <td class="text-end">
                                        <?php if (user_is_admin()): ?>
                                            <div class="table-actions">
                                                <a href="<?= e(url('/products/edit?id=' . $product['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                                <form method="post" action="<?= e(url('/products/delete')); ?>" onsubmit="return confirm('Archiver ce produit ?');">
                                                    <?= csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?= e((string) $product['id']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Archiver</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Lecture seule</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Alerte stock bas</h3>
                <p class="text-muted mb-0">Produits à réapprovisionner en priorité.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($lowStockProducts === []): ?>
                    <div class="alert alert-success mb-0">Aucun produit n’est actuellement sous le seuil minimum.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($lowStockProducts as $product): ?>
                            <div class="list-group-item px-0">
                                <div class="fw-semibold"><?= e($product['name']); ?></div>
                                <small class="text-muted">Stock : <?= e(number_format((float) $product['current_stock'], 2, ',', ' ')); ?> / Min : <?= e(number_format((float) $product['minimum_stock'], 2, ',', ' ')); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
