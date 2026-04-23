<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Enregistrements</h3>
            <p class="text-muted mb-0">Historique des approvisionnements et état de traitement.</p>
        </div>
        <span class="muted-label"><?= e((string) count($procurements)); ?> enregistrement(s)</span>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Approvisionnement</th>
                        <th>Statut</th>
                        <th data-mobile-hidden="true">Paiement</th>
                        <th data-mobile-hidden="true">Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($procurements as $procurement): ?>
                        <?php $canDelete = user_is_admin() && !in_array($procurement['status'], ['received', 'cancelled'], true); ?>
                        <?php $canPayDebt = (float) ($procurement['balance_due'] ?? 0) > 0; ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($procurement['procurement_number']); ?></div>
                                    <div class="table-cell-meta"><?= e($procurement['supplier_name']); ?></div>
                                    <div class="table-cell-meta">Date : <?= e(date('d/m/Y', strtotime((string) $procurement['procurement_date']))); ?></div>
                                    <div class="table-cell-meta">Par <?= e($procurement['user_name']); ?></div>
                                    <div class="table-cell-meta">Payé : <?= e(number_format((float) ($procurement['amount_paid'] ?? 0), 2, ',', ' ')); ?> • Solde : <?= e(number_format((float) ($procurement['balance_due'] ?? 0), 2, ',', ' ')); ?></div>
                                </div>
                            </td>
                            <td><span class="badge <?= e(status_badge_class($procurement['status'])); ?>"><?= e(status_label($procurement['status'])); ?></span></td>
                            <td><span class="badge <?= e(status_badge_class($procurement['payment_status'] ?? 'paid')); ?>"><?= e(status_label($procurement['payment_status'] ?? 'paid')); ?></span></td>
                            <td><?= e(number_format((float) $procurement['grand_total'], 2, ',', ' ')); ?></td>
                            <td class="text-end">
                                <div class="table-actions justify-content-end">
                                    <a href="<?= e(url('/procurements/show?id=' . $procurement['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Voir</a>
                                    <?php if ($canPayDebt): ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-success table-action-btn js-open-procurement-payment-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#procurementPaymentModal"
                                            data-procurement-id="<?= e((string) $procurement['id']); ?>"
                                            data-procurement-number="<?= e($procurement['procurement_number']); ?>"
                                            data-supplier-name="<?= e($procurement['supplier_name']); ?>"
                                            data-balance-due="<?= e(number_format((float) ($procurement['balance_due'] ?? 0), 2, '.', '')); ?>"
                                            data-balance-label="<?= e(number_format((float) ($procurement['balance_due'] ?? 0), 2, ',', ' ')); ?>">
                                            Régler
                                        </button>
                                    <?php endif; ?>
                                    <?php if (user_is_admin()): ?>
                                        <a href="<?= e(url('/procurements/edit?id=' . $procurement['id'])); ?>" class="btn btn-sm btn-outline-secondary table-action-btn">Modifier</a>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                        <form method="post" action="<?= e(url('/procurements/delete')); ?>" onsubmit="return confirm('Supprimer cet approvisionnement ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $procurement['id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Supprimer</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Lecture seule</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="procurementPaymentModal" tabindex="-1" aria-labelledby="procurementPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 mb-1" id="procurementPaymentModalLabel">Régler une dette fournisseur</h2>
                    <p class="text-muted mb-0 small">Enregistrer un paiement sans quitter la liste des approvisionnements.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="document-party-card mb-3">
                    <div class="document-section-label mb-2">Approvisionnement sélectionné</div>
                    <div class="document-meta-list">
                        <span id="procurementPaymentNumber">—</span>
                        <span id="procurementPaymentSupplierName">—</span>
                        <span>Solde restant : <strong id="procurementPaymentBalanceLabel">0,00</strong></span>
                    </div>
                </div>

                <form method="post" action="<?= e(url('/procurements/pay')); ?>" class="row g-3" id="procurementPaymentForm">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="procurement_id" id="procurementPaymentId" value="">

                    <div class="col-md-6">
                        <label class="form-label" for="procurement_payment_date">Date règlement</label>
                        <input type="date" class="form-control" id="procurement_payment_date" name="payment_date" value="<?= e(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="procurement_payment_amount">Montant</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="procurement_payment_amount" name="amount" value="" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="procurement_payment_method">Mode de règlement</label>
                        <select name="method" id="procurement_payment_method" class="form-select" required>
                            <option value="cash">Espèces</option>
                            <option value="bank_transfer">Banque</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Carte</option>
                            <option value="cheque">Chèque</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="procurement_payment_reference">Référence</label>
                        <input type="text" class="form-control" id="procurement_payment_reference" name="reference" placeholder="Ex. reçu, transaction, virement">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="procurement_payment_notes">Note</label>
                        <textarea class="form-control" id="procurement_payment_notes" name="notes" rows="2" placeholder="Observation sur le règlement fournisseur"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer le règlement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('procurementPaymentModal');

    if (!modal) {
        return;
    }

    const paymentIdInput = document.getElementById('procurementPaymentId');
    const paymentNumber = document.getElementById('procurementPaymentNumber');
    const supplierName = document.getElementById('procurementPaymentSupplierName');
    const balanceLabel = document.getElementById('procurementPaymentBalanceLabel');
    const amountInput = document.getElementById('procurement_payment_amount');

    document.querySelectorAll('.js-open-procurement-payment-modal').forEach(function (button) {
        button.addEventListener('click', function () {
            const procurementId = button.dataset.procurementId || '';
            const procurementNumber = button.dataset.procurementNumber || '—';
            const supplier = button.dataset.supplierName || '—';
            const balanceDue = button.dataset.balanceDue || '';
            const balanceDueLabel = button.dataset.balanceLabel || '0,00';

            paymentIdInput.value = procurementId;
            paymentNumber.textContent = procurementNumber;
            supplierName.textContent = supplier;
            balanceLabel.textContent = balanceDueLabel;
            amountInput.value = balanceDue;
            amountInput.max = balanceDue;
        });
    });
});
</script>