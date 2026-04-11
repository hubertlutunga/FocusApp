<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Paiements</h3>
            <p class="text-muted mb-0">Encaissements sur factures validées ou partiellement payées.</p>
        </div>
        <a href="<?= e(url('/payments/create')); ?>" class="btn btn-primary">Nouveau paiement</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive mb-4">
            <table class="table table-striped align-middle js-datatable">
                <thead><tr><th>Paiement</th><th>Montant</th></tr></thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($payment['payment_number']); ?></div>
                                    <div class="table-cell-meta">Facture : <?= e($payment['invoice_number']); ?></div>
                                    <div class="table-cell-meta"><?= e($payment['client_name']); ?></div>
                                    <div class="table-cell-meta"><?= e(date('d/m/Y', strtotime((string) $payment['payment_date']))); ?> • <?= e(payment_method_label($payment['method'])); ?> • <?= e($payment['user_name']); ?></div>
                                </div>
                            </td>
                            <td><?= e(number_format((float) $payment['amount'], 2, ',', ' ')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="alert alert-light mb-0">
            <div class="fw-semibold mb-2">Factures ouvertes</div>
            <?php if ($openInvoices === []): ?>
                <div>Aucune facture à encaisser.</div>
            <?php else: ?>
                <?php foreach ($openInvoices as $invoice): ?>
                    <div><?= e($invoice['invoice_number'] . ' — ' . $invoice['client_name'] . ' — Solde : ' . number_format((float) $invoice['balance_due'], 2, ',', ' ')); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
