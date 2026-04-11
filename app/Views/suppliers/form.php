<?php $supplier = $supplier ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Fournisseur'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour une fiche fournisseur.</p>
        </div>
        <a href="<?= e(url('/suppliers')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($supplier['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $supplier['id']); ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label" for="company_name">Nom du fournisseur</label>
                <input class="form-control" id="company_name" name="company_name" value="<?= e(old('company_name', (string) ($supplier['company_name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="contact_name">Contact principal</label>
                <input class="form-control" id="contact_name" name="contact_name" value="<?= e(old('contact_name', (string) ($supplier['contact_name'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="phone">Téléphone</label>
                <input class="form-control" id="phone" name="phone" value="<?= e(old('phone', (string) ($supplier['phone'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email', (string) ($supplier['email'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="city">Ville</label>
                <input class="form-control" id="city" name="city" value="<?= e(old('city', (string) ($supplier['city'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="address">Adresse</label>
                <input class="form-control" id="address" name="address" value="<?= e(old('address', (string) ($supplier['address'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= e(old('notes', (string) ($supplier['notes'] ?? ''))); ?></textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= (int) old('is_active', (string) ($supplier['is_active'] ?? '1')) === 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Fournisseur actif</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
