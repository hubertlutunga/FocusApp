<?php

$selectedInvoice = (int) old('invoice_id', (string) ($_GET['invoice_id'] ?? '0'));
$selectedInvoiceData = null;

foreach ($openInvoices as $invoiceOption) {
    if ($selectedInvoice === (int) $invoiceOption['id']) {
        $selectedInvoiceData = $invoiceOption;
        break;
    }
}

?>



<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Nouveau paiement</h3>
            <p class="text-muted mb-0">Encaisser une facture validée ou partiellement réglée.</p>
        </div>
        <a href="<?= e(url('/payments')); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e($formAction); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <div class="col-md-6">
                <label class="form-label" for="invoice_id">Facture</label>
                <select class="form-select" id="invoice_id" name="invoice_id" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($openInvoices as $invoice): ?>
                        <option value="<?= e((string) $invoice['id']); ?>" data-balance="<?= e((string) $invoice['balance_due']); ?>" <?= $selectedInvoice === (int) $invoice['id'] ? 'selected' : ''; ?>><?= e($invoice['invoice_number'] . ' — ' . $invoice['client_name'] . ' — Solde : ' . number_format((float) $invoice['balance_due'], 2, ',', ' ')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="payment_date">Date paiement</label>
                <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= e(old('payment_date', date('Y-m-d'))); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="amount">Montant</label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="<?= e(old('amount', $selectedInvoiceData ? (string) $selectedInvoiceData['balance_due'] : '')); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="method">Méthode</label>
                <?php $method = old('method', 'cash'); ?>
                <select class="form-select" id="method" name="method">
                    <option value="cash" <?= $method === 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="mobile_money" <?= $method === 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                    <option value="bank_transfer" <?= $method === 'bank_transfer' ? 'selected' : ''; ?>>Virement</option>
                    <option value="card" <?= $method === 'card' ? 'selected' : ''; ?>>Carte</option>
                    <option value="cheque" <?= $method === 'cheque' ? 'selected' : ''; ?>>Chèque</option>
                    <option value="other" <?= $method === 'other' ? 'selected' : ''; ?>>Autre</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="reference">Référence</label>
                <input class="form-control" id="reference" name="reference" value="<?= e(old('reference', '')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="notes">Note</label>
                <input class="form-control" id="notes" name="notes" value="<?= e(old('notes', '')); ?>">
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Enregistrer le paiement</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceSelect = document.getElementById('invoice_id');
    const amountInput = document.getElementById('amount');

    if (!invoiceSelect || !amountInput) {
        return;
    }

    invoiceSelect.addEventListener('change', function () {
        const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
        if (!selectedOption) {
            return;
        }

        const balance = selectedOption.dataset.balance || '';
        if (balance !== '') {
            amountInput.value = balance;
        }
    });
});
</script>
