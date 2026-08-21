<?php $subscription = $subscription ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1"><?= e($pageTitle ?? 'Abonnement Starlink'); ?></h3>
            <p class="text-muted mb-0">Créer ou mettre à jour une ligne d'abonnement client.</p>
        </div>
        <a href="<?= e(url('/starlink-subscriptions')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <?php if (!empty($subscription['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $subscription['id']); ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label" for="client_id">Client</label>
                <select class="form-select" id="client_id" name="client_id" required>
                    <option value="">Sélectionner un client</option>
                    <?php $selectedClient = (string) old('client_id', (string) ($subscription['client_id'] ?? '')); ?>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']); ?>" <?= $selectedClient === (string) $client['id'] ? 'selected' : ''; ?>>
                            <?= e($client['company_name'] . ' (' . $client['client_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="line_label">Libellé ligne Starlink</label>
                <input class="form-control" id="line_label" name="line_label" value="<?= e(old('line_label', (string) ($subscription['line_label'] ?? ''))); ?>" placeholder="Ex: Starlink Boutique Gombe" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="subscription_number">Numéro abonnement</label>
                <input class="form-control" id="subscription_number" name="subscription_number" value="<?= e(old('subscription_number', (string) ($subscription['subscription_number'] ?? ''))); ?>" placeholder="Ex: STAR-2026-001">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="plan_name">Plan</label>
                <input class="form-control" id="plan_name" name="plan_name" value="<?= e(old('plan_name', (string) ($subscription['plan_name'] ?? ''))); ?>" placeholder="Ex: Business Priority">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="monthly_amount">Montant mensuel</label>
                <input type="number" step="0.01" min="0" class="form-control" id="monthly_amount" name="monthly_amount" value="<?= e(old('monthly_amount', (string) ($subscription['monthly_amount'] ?? '0'))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="start_date">Date début</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= e(old('start_date', (string) ($subscription['start_date'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="end_date">Date échéance</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= e(old('end_date', (string) ($subscription['end_date'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="reminder_days">Alerte (jours avant)</label>
                <input type="number" min="0" max="90" class="form-control" id="reminder_days" name="reminder_days" value="<?= e(old('reminder_days', (string) ($subscription['reminder_days'] ?? '7'))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Statut</label>
                <?php $status = (string) old('status', (string) ($subscription['status'] ?? 'active')); ?>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="expired" <?= $status === 'expired' ? 'selected' : ''; ?>>Expiré</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= e(old('notes', (string) ($subscription['notes'] ?? ''))); ?></textarea>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?= e($submitLabel); ?></button>
            </div>
        </form>
    </div>
</div>
