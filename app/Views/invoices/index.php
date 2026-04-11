<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Factures</h3>
            <p class="text-muted mb-0">Suivi des factures, validation stock et règlements.</p>
        </div>
        <a href="<?= e(url('/invoices/create')); ?>" class="btn btn-primary">Nouvelle facture</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Facture</th>
                        <th data-mobile-hidden="true">Statut</th>
                        <th>Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($invoice['invoice_number']); ?></div>
                                    <div class="table-cell-meta"><?= e($invoice['client_name']); ?></div>
                                    <div class="table-cell-meta"><?= e(date('d/m/Y', strtotime((string) $invoice['invoice_date']))); ?></div>
                                </div>
                            </td>
                            <td><span class="badge <?= e(status_badge_class($invoice['status'])); ?>"><?= e(status_label($invoice['status'])); ?></span></td>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e(number_format((float) $invoice['grand_total'], 2, ',', ' ')); ?></div>
                                    <div class="table-cell-meta">Payé : <?= e(number_format((float) $invoice['amount_paid'], 2, ',', ' ')); ?></div>
                                    <div class="table-cell-meta">Solde : <?= e(number_format((float) $invoice['balance_due'], 2, ',', ' ')); ?></div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(url('/invoices/show?id=' . $invoice['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Voir</a>
                                    <a href="<?= e(url('/invoices/pdf?id=' . $invoice['id'])); ?>" target="_blank" class="btn btn-sm btn-outline-secondary table-action-btn">PDF</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
