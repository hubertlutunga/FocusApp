<?php
$oldItems = old_array('items');
if ($oldItems === []) {
    $oldItems = [['product_id' => '', 'quantity' => 1, 'unit_cost' => 0]];
}
$selectedSupplier = (int) old('supplier_id', '0');
$statusValue = old('status', 'draft');
$paymentMethodValue = old('payment_method', 'cash');
?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Nouvel approvisionnement</h3>
            <p class="text-muted mb-0">Saisir un achat fournisseur et réceptionner immédiatement si besoin.</p>
        </div>
        <a href="<?= e(url('/procurements')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3" id="procurementForm">
            <?= csrf_field(); ?>
            <div class="col-md-4">
                <label class="form-label" for="supplier_id">Fournisseur</label>
                <select class="form-select" id="supplier_id" name="supplier_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= e((string) $supplier['id']); ?>" <?= $selectedSupplier === (int) $supplier['id'] ? 'selected' : ''; ?>><?= e($supplier['company_name'] . ' (' . $supplier['supplier_code'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="procurement_date">Date approvisionnement</label>
                <input type="date" class="form-control" id="procurement_date" name="procurement_date" value="<?= e(old('procurement_date', date('Y-m-d'))); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="expected_date">Date attendue</label>
                <input type="date" class="form-control" id="expected_date" name="expected_date" value="<?= e(old('expected_date', '')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="draft" <?= $statusValue === 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                    <option value="ordered" <?= $statusValue === 'ordered' ? 'selected' : ''; ?>>Commandé</option>
                    <option value="received" <?= $statusValue === 'received' ? 'selected' : ''; ?>>Reçu immédiatement</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="payment_method">Mode de règlement</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="cash" <?= $paymentMethodValue === 'cash' ? 'selected' : ''; ?>>Espèces</option>
                    <option value="bank_transfer" <?= in_array($paymentMethodValue, ['bank', 'bank_transfer'], true) ? 'selected' : ''; ?>>Banque</option>
                    <option value="mobile_money" <?= $paymentMethodValue === 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                    <option value="card" <?= $paymentMethodValue === 'card' ? 'selected' : ''; ?>>Carte</option>
                    <option value="cheque" <?= $paymentMethodValue === 'cheque' ? 'selected' : ''; ?>>Chèque</option>
                    <option value="other" <?= $paymentMethodValue === 'other' ? 'selected' : ''; ?>>Autre</option>
                    <option value="credit" <?= $paymentMethodValue === 'credit' ? 'selected' : ''; ?>>À crédit</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="notes">Notes</label>
                <input class="form-control" id="notes" name="notes" value="<?= e(old('notes', '')); ?>">
            </div>

            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Lignes produits</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addProcurementRow">Ajouter une ligne</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle" id="procurementItemsTable">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Qté</th>
                                <th>Coût unitaire</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($oldItems as $index => $item): ?>
                                <tr>
                                    <td>
                                        <select class="form-select product-select" name="items[product_id][]" required>
                                            <option value="">Sélectionner</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= e((string) $product['id']); ?>" data-cost="<?= e((string) $product['cost_price']); ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : ''; ?>><?= e($product['name'] . ' (' . $product['sku'] . ')'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" min="0.01" class="form-control quantity-input" name="items[quantity][]" value="<?= e((string) ($item['quantity'] ?? '1')); ?>" required></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control cost-input" name="items[unit_cost][]" value="<?= e((string) ($item['unit_cost'] ?? '0')); ?>" required></td>
                                    <td><input type="text" class="form-control line-total" value="<?= e(number_format(((float) ($item['quantity'] ?? 0) * (float) ($item['unit_cost'] ?? 0)), 2, '.', '')); ?>" readonly></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Enregistrer l’approvisionnement</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#procurementItemsTable tbody');
    const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function recalculateRow(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value || '0');
        const cost = parseFloat(row.querySelector('.cost-input').value || '0');
        row.querySelector('.line-total').value = (quantity * cost).toFixed(2);
    }

    function bindRow(row) {
        row.querySelector('.product-select').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const costInput = row.querySelector('.cost-input');
            if (costInput.value === '' || parseFloat(costInput.value) === 0) {
                costInput.value = selected.dataset.cost || '0';
            }
            recalculateRow(row);
        });
        row.querySelector('.quantity-input').addEventListener('input', function () { recalculateRow(row); });
        row.querySelector('.cost-input').addEventListener('input', function () { recalculateRow(row); });
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (tableBody.querySelectorAll('tr').length > 1) {
                row.remove();
            }
        });
        recalculateRow(row);
    }

    document.querySelectorAll('#procurementItemsTable tbody tr').forEach(bindRow);

    document.getElementById('addProcurementRow').addEventListener('click', function () {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select class="form-select product-select" name="items[product_id][]" required>
                    <option value="">Sélectionner</option>
                    ${products.map(product => `<option value="${product.id}" data-cost="${product.cost_price}">${product.name} (${product.sku})</option>`).join('')}
                </select>
            </td>
            <td><input type="number" step="0.01" min="0.01" class="form-control quantity-input" name="items[quantity][]" value="1" required></td>
            <td><input type="number" step="0.01" min="0" class="form-control cost-input" name="items[unit_cost][]" value="0" required></td>
            <td><input type="text" class="form-control line-total" value="0.00" readonly></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button></td>
        `;
        tableBody.appendChild(row);
        bindRow(row);
    });
});
</script>
