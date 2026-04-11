<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Dette tiers</h3>
                <p class="text-muted mb-0">Résumé de la dépense et du solde à régler.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <dl class="row mb-0 small">
                    <dt class="col-5">Numéro</dt><dd class="col-7"><?= e($expense['expense_number']); ?></dd>
                    <dt class="col-5">Date</dt><dd class="col-7"><?= e(date('d/m/Y', strtotime((string) $expense['expense_date']))); ?></dd>
                    <dt class="col-5">Catégorie</dt><dd class="col-7"><?= e($expense['category_name']); ?></dd>
                    <dt class="col-5">Tiers</dt><dd class="col-7"><?= e($expense['supplier_name'] ?? 'Aucun'); ?></dd>
                    <dt class="col-5">Créé par</dt><dd class="col-7"><?= e($expense['user_name']); ?></dd>
                    <dt class="col-5">Mode initial</dt><dd class="col-7"><?= e(payment_method_label($expense['payment_method'])); ?></dd>
                    <dt class="col-5">Statut</dt><dd class="col-7"><span class="badge <?= e(status_badge_class($expense['payment_status'])); ?>"><?= e(status_label($expense['payment_status'])); ?></span></dd>
                    <dt class="col-5">Montant</dt><dd class="col-7 fw-semibold"><?= e(number_format((float) $expense['amount'], 2, ',', ' ')); ?></dd>
                    <dt class="col-5">Déjà payé</dt><dd class="col-7"><?= e(number_format((float) $expense['amount_paid'], 2, ',', ' ')); ?></dd>
                    <dt class="col-5">Solde</dt><dd class="col-7 text-danger fw-semibold"><?= e(number_format((float) $expense['balance_due'], 2, ',', ' ')); ?></dd>
                </dl>
                <hr>
                <div class="small text-muted"><?= e($expense['description']); ?></div>

                <div class="d-flex gap-2 mt-4">
                    <a href="<?= e(url('/expenses')); ?>" class="btn btn-outline-secondary">Retour</a>
                    <a href="<?= e(url('/expenses/edit?id=' . (int) $expense['id'])); ?>" class="btn btn-outline-primary">Modifier</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Règlements</h3>
                <p class="text-muted mb-0">Historique des paiements enregistrés sur cette dette.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($payments === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-cash-coin"></i>
                        <div class="fw-semibold mb-1">Aucun règlement enregistré</div>
                        <p class="mb-0">Le solde reste ouvert tant qu’aucun paiement n’est saisi.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Référence</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e($payment['payment_number']); ?></div>
                                            <small class="text-muted">Par <?= e($payment['user_name']); ?></small>
                                        </td>
                                        <td><?= e(date('d/m/Y', strtotime((string) $payment['payment_date']))); ?></td>
                                        <td><span class="badge <?= e(payment_method_badge_class($payment['method'])); ?>"><?= e(payment_method_label($payment['method'])); ?></span></td>
                                        <td><?= e($payment['reference'] ?: '—'); ?></td>
                                        <td class="text-end fw-semibold text-amount"><?= e(number_format((float) $payment['amount'], 2, ',', ' ')); ?></td>
                                    </tr>
                                    <?php if (!empty($payment['notes'])): ?>
                                        <tr>
                                            <td colspan="5" class="small text-muted pt-0"><?= e($payment['notes']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ((float) $expense['balance_due'] > 0): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Enregistrer un règlement</h3>
                <p class="text-muted mb-0">Solde restant : <?= e(number_format((float) $expense['balance_due'], 2, ',', ' ')); ?></p>
            </div>
            <div class="card-body px-4 pb-4">
                <form method="post" action="<?= e(url('/expenses/pay')); ?>" class="row g-3">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="expense_id" value="<?= e((string) $expense['id']); ?>">
                    <div class="col-md-4">
                        <label class="form-label">Date règlement</label>
                        <input type="date" class="form-control" name="payment_date" value="<?= e(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Montant</label>
                        <input type="number" step="0.01" min="0.01" max="<?= e((string) $expense['balance_due']); ?>" class="form-control" name="amount" value="<?= e((string) $expense['balance_due']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mode de règlement</label>
                        <select name="method" class="form-select" required>
                            <option value="cash">Espèces</option>
                            <option value="bank_transfer">Banque</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Carte</option>
                            <option value="cheque">Chèque</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Référence</label>
                        <input type="text" class="form-control" name="reference" placeholder="Ex. reçu, transaction, virement">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Note</label>
                        <input type="text" class="form-control" name="notes" placeholder="Observation sur le règlement">
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Enregistrer le règlement</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>