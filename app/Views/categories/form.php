<?php $category = $category ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Catégorie'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour une catégorie métier.</p>
        </div>
        <a href="<?= e(url('/categories')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($category['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $category['id']); ?>">
            <?php endif; ?>

            <div class="col-md-4">
                <label class="form-label" for="type">Type</label>
                <?php $selectedType = old('type', (string) ($category['type'] ?? 'product')); ?>
                <select class="form-select" id="type" name="type" required>
                    <option value="product" <?= $selectedType === 'product' ? 'selected' : ''; ?>>Produit</option>
                    <option value="service" <?= $selectedType === 'service' ? 'selected' : ''; ?>>Service</option>
                    <option value="mixed" <?= $selectedType === 'mixed' ? 'selected' : ''; ?>>Mixte</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="name">Nom</label>
                <input class="form-control" id="name" name="name" value="<?= e(old('name', (string) ($category['name'] ?? ''))); ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= e(old('description', (string) ($category['description'] ?? ''))); ?></textarea>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
