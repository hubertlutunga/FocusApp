<?php $unit = $unit ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Unité'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour une unité de mesure.</p>
        </div>
        <a href="<?= e(url('/units')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($unit['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $unit['id']); ?>">
            <?php endif; ?>

            <div class="col-md-8">
                <label class="form-label" for="name">Nom</label>
                <input class="form-control" id="name" name="name" value="<?= e(old('name', (string) ($unit['name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="symbol">Symbole</label>
                <input class="form-control" id="symbol" name="symbol" value="<?= e(old('symbol', (string) ($unit['symbol'] ?? ''))); ?>" required>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
