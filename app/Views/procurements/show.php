<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Approvisionnement</h3>
                <p class="text-muted mb-0">Résumé et statut de la commande fournisseur.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <dl class="row mb-0 small">
                    <dt class="col-5">Numéro</dt><dd class="col-7"><?= e($procurement['procurement_number']); ?></dd>
                    <dt class="col-5">Fournisseur</dt><dd class="col-7"><?= e($procurement['supplier_name']); ?></dd>
                    <dt class="col-5">Date</dt><dd class="col-7"><?= e(date('d/m/Y', strtotime((string) $procurement['procurement_date']))); ?></dd>
                    <dt class="col-5">Attendue</dt><dd class="col-7"><?= e($procurement['expected_date'] ? date('d/m/Y', strtotime((string) $procurement['expected_date'])) : '—'); ?></dd>
                    <dt class="col-5">Reçue</dt><dd class="col-7"><?= e($procurement['received_date'] ? date('d/m/Y', strtotime((string) $procurement['received_date'])) : '—'); ?></dd>
                    <dt class="col-5">Statut</dt><dd class="col-7"><span class="badge <?= e(status_badge_class($procurement['status'])); ?>"><?= e(status_label($procurement['status'])); ?></span></dd>
                    <dt class="col-5">Règlement</dt><dd class="col-7"><span class="badge <?= e(status_badge_class($procurement['payment_status'] ?? 'paid')); ?>"><?= e(status_label($procurement['payment_status'] ?? 'paid')); ?></span></dd>
                    <dt class="col-5">Mode initial</dt><dd class="col-7"><?= e(payment_method_label($procurement['payment_method'] ?? 'cash')); ?></dd>
                    <dt class="col-5">Créé par</dt><dd class="col-7"><?= e($procurement['user_name']); ?></dd>
                    <dt class="col-5">Total</dt><dd class="col-7 fw-semibold"><?= e(number_format((float) $procurement['grand_total'], 2, ',', ' ')); ?></dd>
                    <dt class="col-5">Déjà payé</dt><dd class="col-7"><?= e(number_format((float) ($procurement['amount_paid'] ?? 0), 2, ',', ' ')); ?></dd>
                    <dt class="col-5">Solde</dt><dd class="col-7 text-danger fw-semibold"><?= e(number_format((float) ($procurement['balance_due'] ?? 0), 2, ',', ' ')); ?></dd>
                </dl>
                <?php if ($procurement['notes']): ?>
                    <hr>
                    <div class="small text-muted"><?= e($procurement['notes']); ?></div>
                <?php endif; ?>
                <div class="d-flex gap-2 mt-4">
                    <?php if ($procurement['status'] !== 'received' && $procurement['status'] !== 'cancelled'): ?>
                        <?php if (user_is_admin()): ?>
                            <form method="post" action="<?= e(url('/procurements/receive')); ?>">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= e((string) $procurement['id']); ?>">
                                <button type="submit" class="btn btn-primary">Marquer reçu</button>
                            </form>
                            <form method="post" action="<?= e(url('/procurements/cancel')); ?>" onsubmit="return confirm('Annuler cet approvisionnement ?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= e((string) $procurement['id']); ?>">
                                <button type="submit" class="btn btn-outline-danger">Annuler</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?= e(url('/procurements')); ?>" class="btn btn-outline-secondary">Retour</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Lignes produits</h3>
                <p class="text-muted mb-0">Détail quantités et coûts unitaires.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Qté</th>
                                <th>Coût unitaire</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($item['product_name']); ?></div>
                                        <small class="text-muted"><?= e($item['sku']); ?></small>
                                    </td>
                                    <td><?= e(number_format((float) $item['quantity'], 2, ',', ' ') . ' ' . $item['unit_symbol']); ?></td>
                                    <td><?= e(number_format((float) $item['unit_cost'], 2, ',', ' ')); ?></td>
                                    <td><?= e(number_format((float) $item['line_total'], 2, ',', ' ')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Règlements fournisseur</h3>
                <p class="text-muted mb-0">Historique des paiements liés à cet approvisionnement.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($payments === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-cash-coin"></i>
                        <div class="fw-semibold mb-1">Aucun règlement enregistré</div>
                        <p class="mb-0">Le solde fournisseur reste ouvert tant qu’aucun règlement n’est saisi.</p>
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

        <?php if ((float) ($procurement['balance_due'] ?? 0) > 0): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Enregistrer un règlement fournisseur</h3>
                <p class="text-muted mb-0">Solde restant : <?= e(number_format((float) $procurement['balance_due'], 2, ',', ' ')); ?></p>
            </div>
            <div class="card-body px-4 pb-4">
                <form method="post" action="<?= e(url('/procurements/pay')); ?>" class="row g-3">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="procurement_id" value="<?= e((string) $procurement['id']); ?>">
                    <div class="col-md-4">
                        <label class="form-label">Date règlement</label>
                        <input type="date" class="form-control" name="payment_date" value="<?= e(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Montant</label>
                        <input type="number" step="0.01" min="0.01" max="<?= e((string) $procurement['balance_due']); ?>" class="form-control" name="amount" value="<?= e((string) $procurement['balance_due']); ?>" required>
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
                        <input type="text" class="form-control" name="notes" placeholder="Observation sur le règlement fournisseur">
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
