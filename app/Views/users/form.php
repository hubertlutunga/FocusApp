<?php $userData = $userData ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Utilisateur'); ?></h3>
            <p class="text-muted mb-0">Créez ou modifiez un compte applicatif.</p>
        </div>
        <a href="<?= e(url('/users')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($userData['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $userData['id']); ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label" for="full_name">Nom complet</label>
                <input class="form-control" id="full_name" name="full_name" value="<?= e(old('full_name', (string) ($userData['full_name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="role_id">Profil</label>
                <?php $selectedRoleId = (int) old('role_id', (string) ($userData['role_id'] ?? '0')); ?>
                <select class="form-select" id="role_id" name="role_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e((string) $role['id']); ?>" <?= $selectedRoleId === (int) $role['id'] ? 'selected' : ''; ?>><?= e($role['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email', (string) ($userData['email'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Téléphone</label>
                <input class="form-control" id="phone" name="phone" value="<?= e(old('phone', (string) ($userData['phone'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Mot de passe<?= $passwordRequired ? '' : ' (laisser vide pour conserver l’actuel)'; ?></label>
                <input type="password" class="form-control" id="password" name="password" <?= $passwordRequired ? 'required' : ''; ?>>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" <?= $passwordRequired ? 'required' : ''; ?>>
            </div>
            <div class="col-12">
                <?php $isActive = old_value('is_active', (int) ($userData['is_active'] ?? 1)) ? true : false; ?>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= $isActive ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Compte actif</label>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>