<?php $editing = isset($expense['id']); ?>
<?php $supportsCreditTracking = $supportsCreditTracking ?? true; ?>
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
                <div class="muted-label">
                    Les champs catégorie, description et montant sont obligatoires.
                    <?= $supportsCreditTracking ? 'Le mode À crédit ouvre un suivi de solde.' : 'Le mode À crédit sera disponible après migration de la base.' ?>
                </div>
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
                <div class="d-flex gap-2 align-items-start">
                    <select name="supplier_id" id="supplier_id" class="form-select">
                        <option value="">Aucun</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <?php $selected = (string) old('supplier_id', (string) ($expense['supplier_id'] ?? '')) === (string) $supplier['id']; ?>
                            <option value="<?= e((string) $supplier['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($supplier['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#expenseSupplierModal">
                        <i class="bi bi-plus-lg"></i>
                        Nouveau
                    </button>
                </div>
                <div class="form-text">Ajoutez un fournisseur sans quitter cette dépense.</div>
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
                        <?php if ($supportsCreditTracking): ?>
                            <option value="credit" <?= $paymentMethod === 'credit' ? 'selected' : '' ?>>À crédit</option>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
                <?php if (!$editing && !$supportsCreditTracking): ?>
                    <div class="form-text">Le suivi des dettes tiers sera activé après migration.</div>
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

<div class="modal fade" id="expenseSupplierModal" tabindex="-1" aria-labelledby="expenseSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 mb-1" id="expenseSupplierModalLabel">Nouveau fournisseur</h2>
                    <p class="text-muted mb-0">Création rapide depuis la saisie de dépense.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-danger d-none" id="expenseSupplierModalError" role="alert"></div>
                <form id="expenseSupplierForm" class="row g-3" action="<?= e(url('/expenses/suppliers/store')) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="col-md-6">
                        <label class="form-label" for="modal_supplier_company_name">Nom du fournisseur</label>
                        <input class="form-control" id="modal_supplier_company_name" name="company_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="modal_supplier_contact_name">Contact principal</label>
                        <input class="form-control" id="modal_supplier_contact_name" name="contact_name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="modal_supplier_phone">Téléphone</label>
                        <input class="form-control" id="modal_supplier_phone" name="phone">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="modal_supplier_email">Email</label>
                        <input type="email" class="form-control" id="modal_supplier_email" name="email">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="modal_supplier_city">Ville</label>
                        <input class="form-control" id="modal_supplier_city" name="city">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="modal_supplier_address">Adresse</label>
                        <input class="form-control" id="modal_supplier_address" name="address">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="modal_supplier_notes">Notes</label>
                        <textarea class="form-control" id="modal_supplier_notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="expenseSupplierSubmitBtn">Enregistrer le fournisseur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const supplierForm = document.getElementById('expenseSupplierForm');
    const supplierSelect = document.getElementById('supplier_id');
    const modalElement = document.getElementById('expenseSupplierModal');
    const errorBox = document.getElementById('expenseSupplierModalError');
    const submitButton = document.getElementById('expenseSupplierSubmitBtn');

    if (!supplierForm || !supplierSelect || !modalElement || !submitButton) {
        return;
    }

    const supplierModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    const setError = (message) => {
        if (!errorBox) {
            return;
        }

        if (!message) {
            errorBox.textContent = '';
            errorBox.classList.add('d-none');
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    };

    supplierForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setError('');

        const formData = new FormData(supplierForm);
        submitButton.disabled = true;
        const initialLabel = submitButton.textContent;
        submitButton.textContent = 'Enregistrement...';

        try {
            const response = await fetch(supplierForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const payload = await response.json();

            if (!response.ok || !payload.success || !payload.supplier) {
                throw new Error(payload.message || 'Impossible de créer ce fournisseur.');
            }

            const option = document.createElement('option');
            option.value = String(payload.supplier.id);
            option.textContent = payload.supplier.company_name;
            option.selected = true;
            supplierSelect.appendChild(option);
            supplierSelect.value = option.value;

            supplierForm.reset();
            supplierModal.hide();

            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Fournisseur ajouté',
                    text: payload.message || 'Le fournisseur a été créé avec succès.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        } catch (error) {
            setError(error instanceof Error ? error.message : 'Impossible de créer ce fournisseur.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = initialLabel;
        }
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        supplierForm.reset();
        setError('');
    });
});
</script>
