<?php $product = $product ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Produit'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour un produit en stock.</p>
        </div>
        <a href="<?= e(url('/products')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($product['id'])): ?><input type="hidden" name="id" value="<?= e((string) $product['id']); ?>"><?php endif; ?>
            <div class="col-md-4">
                <label class="form-label" for="sku">SKU</label>
                <input class="form-control" id="sku" name="sku" value="<?= e(old('sku', (string) ($product['sku'] ?? ''))); ?>" placeholder="Auto si vide">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="category_id">Catégorie</label>
                <?php $selectedCategory = (int) old('category_id', (string) ($product['category_id'] ?? '0')); ?>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e((string) $category['id']); ?>" <?= $selectedCategory === (int) $category['id'] ? 'selected' : ''; ?>><?= e($category['name'] . ' (' . $category['type'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="unit_id">Unité</label>
                <?php $selectedUnit = (int) old('unit_id', (string) ($product['unit_id'] ?? '0')); ?>
                <select class="form-select" id="unit_id" name="unit_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= e((string) $unit['id']); ?>" <?= $selectedUnit === (int) $unit['id'] ? 'selected' : ''; ?>><?= e($unit['name'] . ' (' . $unit['symbol'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="name">Nom du produit</label>
                <input class="form-control" id="name" name="name" value="<?= e(old('name', (string) ($product['name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="barcode">Code-barres</label>
                <input class="form-control" id="barcode" name="barcode" value="<?= e(old('barcode', (string) ($product['barcode'] ?? ''))); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="cost_price">Prix d’achat</label>
                <input type="number" step="0.01" min="0" class="form-control" id="cost_price" name="cost_price" value="<?= e(old('cost_price', (string) ($product['cost_price'] ?? '0'))); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="sale_price">Prix de vente</label>
                <input type="number" step="0.01" min="0" class="form-control" id="sale_price" name="sale_price" value="<?= e(old('sale_price', (string) ($product['sale_price'] ?? '0'))); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="minimum_stock">Stock minimum</label>
                <input type="number" step="0.01" min="0" class="form-control" id="minimum_stock" name="minimum_stock" value="<?= e(old('minimum_stock', (string) ($product['minimum_stock'] ?? '0'))); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="current_stock">Stock actuel</label>
                <input type="number" step="0.01" min="0" class="form-control" id="current_stock" name="current_stock" value="<?= e(old('current_stock', (string) ($product['current_stock'] ?? '0'))); ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label" for="image_path">Chemin image</label>
                <input class="form-control" id="image_path" name="image_path" value="<?= e(old('image_path', (string) ($product['image_path'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= e(old('description', (string) ($product['description'] ?? ''))); ?></textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= (int) old('is_active', (string) ($product['is_active'] ?? '1')) === 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Produit actif</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
