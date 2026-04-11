<?php $editing = isset($expense['id']); ?>
<div class="page-hero">
    <div>
        <h1 class="h3 mb-1"><?= $editing ? 'Modifier la dépense' : 'Nouvelle dépense' ?></h1>
        <p class="text-muted mb-0">Renseignez les informations comptables de la dépense.</p>
    </div>
    <a href="<?= e(url('/expenses')) ?>" class="btn btn-outline-secondary">Retour</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex align-items-center gap-3">
            <span class="metric-icon primary"><i class="bi bi-pencil-square"></i></span>
            <div>
                <div class="fw-semibold"><?= $editing ? 'Mise à jour d’une charge existante' : 'Enregistrement d’une nouvelle charge ou dette tiers' ?></div>
                <div class="muted-label">Les champs catégorie, description et montant sont obligatoires. Le mode À crédit ouvre un suivi de solde.</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form action="<?= e($formAction) ?>" method="POST" class="row g-3">
            <?= csrf_field() ?>
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= e((string) $expense['id']) ?>">
            <?php endif; ?>

            <div class="col-md-4">
                <label for="expense_category_id" class="form-label">Catégorie</label>
                <select name="expense_category_id" id="expense_category_id" class="form-select" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($categories as $category): ?>
                        <?php $selected = (string) old('expense_category_id', (string) ($expense['expense_category_id'] ?? '')) === (string) $category['id']; ?>
                        <option value="<?= e((string) $category['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="supplier_id" class="form-label">Tiers / fournisseur</label>
                <select name="supplier_id" id="supplier_id" class="form-select">
                    <option value="">Aucun</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <?php $selected = (string) old('supplier_id', (string) ($expense['supplier_id'] ?? '')) === (string) $supplier['id']; ?>
                        <option value="<?= e((string) $supplier['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($supplier['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="expense_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?= e((string) old('expense_date', $expense['expense_date'] ?? date('Y-m-d'))) ?>" required>
            </div>

            <div class="col-md-8">
                <label for="description" class="form-label">Description / produit / service</label>
                <input type="text" class="form-control" id="description" name="description" value="<?= e((string) old('description', $expense['description'] ?? '')) ?>" required>
            </div>

            <div class="col-md-2">
                <label for="amount" class="form-label">Montant</label>
                <input type="number" step="0.01" min="0" class="form-control" id="amount" name="amount" value="<?= e((string) old('amount', $expense['amount'] ?? '0')) ?>" required>
            </div>

            <div class="col-md-2">
                <label for="payment_method" class="form-label">Paiement</label>
                <?php $paymentMethod = (string) old('payment_method', $expense['payment_method'] ?? 'cash'); ?>
                <?php if ($editing): ?>
                    <input type="hidden" name="payment_method" value="<?= e($paymentMethod) ?>">
                    <input type="text" class="form-control" value="<?= e(payment_method_label($paymentMethod)) ?>" disabled>
                <?php else: ?>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>Espèces</option>
                        <option value="bank_transfer" <?= in_array($paymentMethod, ['bank', 'bank_transfer'], true) ? 'selected' : '' ?>>Banque</option>
                        <option value="mobile_money" <?= $paymentMethod === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                        <option value="card" <?= $paymentMethod === 'card' ? 'selected' : '' ?>>Carte</option>
                        <option value="cheque" <?= $paymentMethod === 'cheque' ? 'selected' : '' ?>>Chèque</option>
                        <option value="other" <?= $paymentMethod === 'other' ? 'selected' : '' ?>>Autre</option>
                        <option value="credit" <?= $paymentMethod === 'credit' ? 'selected' : '' ?>>À crédit</option>
                    </select>
                <?php endif; ?>
            </div>

            <?php if ($editing && isset($expense['payment_status'])): ?>
            <div class="col-md-4">
                <label class="form-label">Statut dette</label>
                <input type="text" class="form-control" value="<?= e(status_label((string) $expense['payment_status'])) ?>" disabled>
            </div>
            <?php endif; ?>

            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                <a href="<?= e(url('/expenses')) ?>" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
            </div>
        </form>
    </div>
</div>
