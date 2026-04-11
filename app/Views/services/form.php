<?php $service = $service ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Service'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour un service technique.</p>
        </div>
        <a href="<?= e(url('/services')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($service['id'])): ?><input type="hidden" name="id" value="<?= e((string) $service['id']); ?>"><?php endif; ?>
            <div class="col-md-4">
                <label class="form-label" for="code">Code service</label>
                <input class="form-control" id="code" name="code" value="<?= e(old('code', (string) ($service['code'] ?? ''))); ?>" placeholder="Auto si vide">
            </div>
            <div class="col-md-8">
                <label class="form-label" for="category_id">Catégorie</label>
                <?php $selectedCategory = (int) old('category_id', (string) ($service['category_id'] ?? '0')); ?>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e((string) $category['id']); ?>" <?= $selectedCategory === (int) $category['id'] ? 'selected' : ''; ?>><?= e($category['name'] . ' (' . $category['type'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="name">Nom du service</label>
                <input class="form-control" id="name" name="name" value="<?= e(old('name', (string) ($service['name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="unit_price">Prix unitaire</label>
                <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="<?= e(old('unit_price', (string) ($service['unit_price'] ?? '0'))); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="estimated_cost">Coût estimé</label>
                <input type="number" step="0.01" min="0" class="form-control" id="estimated_cost" name="estimated_cost" value="<?= e(old('estimated_cost', (string) ($service['estimated_cost'] ?? '0'))); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="duration_hours">Durée (h)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="duration_hours" name="duration_hours" value="<?= e(old('duration_hours', (string) ($service['duration_hours'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= e(old('description', (string) ($service['description'] ?? ''))); ?></textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= (int) old('is_active', (string) ($service['is_active'] ?? '1')) === 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Service actif</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
