<?php $client = $client ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Client'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour une fiche client.</p>
        </div>
        <a href="<?= e(url('/clients')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($client['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $client['id']); ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label" for="company_name">Nom du client</label>
                <input class="form-control" id="company_name" name="company_name" value="<?= e(old('company_name', (string) ($client['company_name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="contact_name">Contact principal</label>
                <input class="form-control" id="contact_name" name="contact_name" value="<?= e(old('contact_name', (string) ($client['contact_name'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="phone">Téléphone</label>
                <input class="form-control" id="phone" name="phone" value="<?= e(old('phone', (string) ($client['phone'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email', (string) ($client['email'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="tax_number">NIF / RCCM</label>
                <input class="form-control" id="tax_number" name="tax_number" value="<?= e(old('tax_number', (string) ($client['tax_number'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="city">Ville</label>
                <input class="form-control" id="city" name="city" value="<?= e(old('city', (string) ($client['city'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="address">Adresse</label>
                <input class="form-control" id="address" name="address" value="<?= e(old('address', (string) ($client['address'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= e(old('notes', (string) ($client['notes'] ?? ''))); ?></textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= (int) old('is_active', (string) ($client['is_active'] ?? '1')) === 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Client actif</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
