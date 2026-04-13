<?php
$oldItems = old_array('items');
if ($oldItems === []) {
    $oldItems = [['item_type' => 'product', 'product_id' => '', 'service_id' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]];
}
$selectedClient = (int) old('client_id', '0');
$statusValue = old('status', 'draft');
$selectedTaxRate = normalize_tax_rate(old_value('tax_rate', 0));
$taxOptions = tax_rate_options();
?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Nouveau devis</h3>
            <p class="text-muted mb-0">Composez une offre avec produits et services.</p>
        </div>
        <a href="<?= e(url('/quotes')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3" id="quoteForm">
            <?= csrf_field(); ?>
            <div class="col-md-4">
                <label class="form-label" for="client_id">Client</label>
                <select class="form-select" id="client_id" name="client_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']); ?>" <?= $selectedClient === (int) $client['id'] ? 'selected' : ''; ?>><?= e($client['company_name'] . ' (' . $client['client_code'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="quote_date">Date devis</label>
                <input type="date" class="form-control" id="quote_date" name="quote_date" value="<?= e(old('quote_date', date('Y-m-d'))); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="valid_until">Valide jusqu’au</label>
                <input type="date" class="form-control" id="valid_until" name="valid_until" value="<?= e(old('valid_until', '')); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="status">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="draft" <?= $statusValue === 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                    <option value="sent" <?= $statusValue === 'sent' ? 'selected' : ''; ?>>Envoyé</option>
                    <option value="approved" <?= $statusValue === 'approved' ? 'selected' : ''; ?>>Approuvé</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="quoteTaxRate">Taxe</label>
                <select class="form-select" id="quoteTaxRate" name="tax_rate">
                    <?php foreach ($taxOptions as $rate => $label): ?>
                        <option value="<?= e((string) $rate); ?>" <?= abs($selectedTaxRate - (float) $rate) < 0.001 ? 'selected' : ''; ?>><?= e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="2"><?= e(old('notes', '')); ?></textarea>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Lignes du devis</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addQuoteRow">Ajouter une ligne</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle" id="quoteItemsTable">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Référence</th>
                                <th>Description</th>
                                <th>Qté</th>
                                <th>Prix unitaire</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($oldItems as $item): ?>
                                <tr>
                                    <td>
                                        <?php $itemType = $item['item_type'] ?? 'product'; ?>
                                        <select class="form-select item-type" name="items[item_type][]">
                                            <option value="product" <?= $itemType === 'product' ? 'selected' : ''; ?>>Produit</option>
                                            <option value="service" <?= $itemType === 'service' ? 'selected' : ''; ?>>Service</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select product-select <?= $itemType === 'service' ? 'd-none' : ''; ?>" name="items[product_id][]">
                                            <option value="">Produit</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= e((string) $product['id']); ?>" data-name="<?= e($product['name']); ?>" data-price="<?= e((string) $product['sale_price']); ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : ''; ?>><?= e($product['name'] . ' (' . $product['sku'] . ')'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select class="form-select service-select <?= $itemType === 'product' ? 'd-none' : ''; ?>" name="items[service_id][]">
                                            <option value="">Service</option>
                                            <?php foreach ($services as $service): ?>
                                                <option value="<?= e((string) $service['id']); ?>" data-name="<?= e($service['name']); ?>" data-price="<?= e((string) $service['unit_price']); ?>" <?= (int) ($item['service_id'] ?? 0) === (int) $service['id'] ? 'selected' : ''; ?>><?= e($service['name'] . ' (' . $service['code'] . ')'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input class="form-control description-input" name="items[description][]" value="<?= e((string) ($item['description'] ?? '')); ?>"></td>
                                    <td><input type="number" step="0.01" min="0.01" class="form-control quantity-input" name="items[quantity][]" value="<?= e((string) ($item['quantity'] ?? '1')); ?>"></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control price-input" name="items[unit_price][]" value="<?= e((string) ($item['unit_price'] ?? '0')); ?>"></td>
                                    <td><input type="text" class="form-control total-input" value="<?= e(number_format(((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 2, '.', '')); ?>" readonly></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-4 ms-lg-auto">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between"><span>Sous-total HT</span><strong id="quoteSubtotal">0.00</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span id="quoteTaxLabel"><?= e(tax_rate_label($selectedTaxRate)); ?></span><strong id="quoteTaxAmount">0.00</strong></div>
                        <div class="d-flex justify-content-between mt-3 pt-3 border-top"><span class="fw-semibold">Total TTC</span><strong class="fs-5" id="quoteGrandTotal">0.00</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Enregistrer le devis</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const services = <?= json_encode($services, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const tableBody = document.querySelector('#quoteItemsTable tbody');
    const taxRateInput = document.getElementById('quoteTaxRate');
    const subtotalOutput = document.getElementById('quoteSubtotal');
    const taxLabelOutput = document.getElementById('quoteTaxLabel');
    const taxAmountOutput = document.getElementById('quoteTaxAmount');
    const grandTotalOutput = document.getElementById('quoteGrandTotal');

    function formatAmount(value) {
        return value.toFixed(2);
    }

    function currentTaxRate() {
        return parseFloat(taxRateInput.value || '0');
    }

    function currentTaxLabel() {
        const selected = taxRateInput.options[taxRateInput.selectedIndex];
        return selected ? selected.textContent : 'Exonere';
    }

    function recalcTotals() {
        let subtotal = 0;

        tableBody.querySelectorAll('tr').forEach(function (row) {
            subtotal += parseFloat(row.querySelector('.total-input').value || '0');
        });

        const taxAmount = subtotal * (currentTaxRate() / 100);
        const grandTotal = subtotal + taxAmount;

        subtotalOutput.textContent = formatAmount(subtotal);
        taxLabelOutput.textContent = currentTaxLabel();
        taxAmountOutput.textContent = formatAmount(taxAmount);
        grandTotalOutput.textContent = formatAmount(grandTotal);
    }

    function recalc(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value || '0');
        const price = parseFloat(row.querySelector('.price-input').value || '0');
        row.querySelector('.total-input').value = (qty * price).toFixed(2);
        recalcTotals();
    }

    function syncType(row) {
        const type = row.querySelector('.item-type').value;
        row.querySelector('.product-select').classList.toggle('d-none', type !== 'product');
        row.querySelector('.service-select').classList.toggle('d-none', type !== 'service');
    }

    function syncReference(row, target) {
        const selected = target.options[target.selectedIndex];
        if (!selected) return;
        const description = row.querySelector('.description-input');
        const price = row.querySelector('.price-input');
        if (description.value === '') description.value = selected.dataset.name || '';
        if (parseFloat(price.value || '0') === 0) price.value = selected.dataset.price || '0';
        recalc(row);
    }

    function bindRow(row) {
        row.querySelector('.item-type').addEventListener('change', function () { syncType(row); });
        row.querySelector('.product-select').addEventListener('change', function () { syncReference(row, this); });
        row.querySelector('.service-select').addEventListener('change', function () { syncReference(row, this); });
        row.querySelector('.quantity-input').addEventListener('input', function () { recalc(row); });
        row.querySelector('.price-input').addEventListener('input', function () { recalc(row); });
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (tableBody.querySelectorAll('tr').length > 1) {
                row.remove();
                recalcTotals();
            }
        });
        syncType(row);
        recalc(row);
    }

    document.querySelectorAll('#quoteItemsTable tbody tr').forEach(bindRow);
    taxRateInput.addEventListener('change', recalcTotals);

    document.getElementById('addQuoteRow').addEventListener('click', function () {
        const productOptions = products.map(product => `<option value="${product.id}" data-name="${product.name}" data-price="${product.sale_price}">${product.name} (${product.sku})</option>`).join('');
        const serviceOptions = services.map(service => `<option value="${service.id}" data-name="${service.name}" data-price="${service.unit_price}">${service.name} (${service.code})</option>`).join('');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><select class="form-select item-type" name="items[item_type][]"><option value="product">Produit</option><option value="service">Service</option></select></td>
            <td>
                <select class="form-select product-select" name="items[product_id][]"><option value="">Produit</option>${productOptions}</select>
                <select class="form-select service-select d-none" name="items[service_id][]"><option value="">Service</option>${serviceOptions}</select>
            </td>
            <td><input class="form-control description-input" name="items[description][]"></td>
            <td><input type="number" step="0.01" min="0.01" class="form-control quantity-input" name="items[quantity][]" value="1"></td>
            <td><input type="number" step="0.01" min="0" class="form-control price-input" name="items[unit_price][]" value="0"></td>
            <td><input type="text" class="form-control total-input" value="0.00" readonly></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button></td>`;
        tableBody.appendChild(row);
        bindRow(row);
    });

    recalcTotals();
});
</script>
