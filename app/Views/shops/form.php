<?php $shop = $shop ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Boutique'); ?></h3>
            <p class="text-muted mb-0">Définissez les informations de l’extension et son statut opérationnel.</p>
        </div>
        <a href="<?= e(url('/shops')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($shop['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $shop['id']); ?>">
            <?php endif; ?>

            <div class="col-md-4">
                <label class="form-label" for="code">Code</label>
                <input class="form-control" id="code" name="code" value="<?= e(old('code', (string) ($shop['code'] ?? ''))); ?>" placeholder="BTQ-GOMBE" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="name">Nom de la boutique</label>
                <input class="form-control" id="name" name="name" value="<?= e(old('name', (string) ($shop['name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="manager_name">Responsable</label>
                <input class="form-control" id="manager_name" name="manager_name" value="<?= e(old('manager_name', (string) ($shop['manager_name'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Téléphone</label>
                <input class="form-control" id="phone" name="phone" value="<?= e(old('phone', (string) ($shop['phone'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="city">Ville</label>
                <input class="form-control" id="city" name="city" value="<?= e(old('city', (string) ($shop['city'] ?? ''))); ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label" for="address">Adresse</label>
                <input class="form-control" id="address" name="address" value="<?= e(old('address', (string) ($shop['address'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <?php $isActive = old_value('is_active', (int) ($shop['is_active'] ?? 1)) ? true : false; ?>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= $isActive ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Boutique active</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>