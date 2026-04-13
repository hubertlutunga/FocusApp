<div class="document-shell">
    <div class="document-hero invoice-document-hero mb-4">
        <div>
            <div class="document-kicker">Facturation</div>
            <h1 class="document-title mb-2">Facture <?= e($invoice['invoice_number']); ?></h1>
            <p class="document-subtitle mb-0">Émise pour <?= e($invoice['client_name']); ?> le <?= e(date('d/m/Y', strtotime((string) $invoice['invoice_date']))); ?>.</p>
        </div>
        <div class="document-hero-side">
            <span class="badge <?= e(status_badge_class($invoice['status'])); ?> document-status-badge"><?= e(status_label($invoice['status'])); ?></span>
            <div class="document-total-card">
                <span>Total TTC</span>
                <strong><?= e(number_format((float) $invoice['grand_total'], 2, ',', ' ')); ?></strong>
            </div>
        </div>
    </div>

    <div class="document-toolbar mb-4">
        <a href="<?= e(url('/invoices/pdf?id=' . $invoice['id'])); ?>" target="_blank" class="btn btn-outline-secondary">PDF</a>
        <?php if ($invoice['status'] === 'draft'): ?>
            <form method="post" action="<?= e(url('/invoices/validate')); ?>">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= e((string) $invoice['id']); ?>">
                <button type="submit" class="btn btn-primary">Valider</button>
            </form>
        <?php endif; ?>
        <?php if (user_is_admin() && $invoice['status'] !== 'cancelled'): ?>
            <form method="post" action="<?= e(url('/invoices/cancel')); ?>" onsubmit="return confirm('Annuler cette facture ?');">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= e((string) $invoice['id']); ?>">
                <button type="submit" class="btn btn-outline-danger">Annuler</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm document-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="row g-4 align-items-start mb-4">
                        <div class="col-md-7">
                            <div class="document-section-label">Client</div>
                            <div class="document-party-card">
                                <h3 class="h5 mb-2"><?= e($invoice['client_name']); ?></h3>
                                <div class="document-meta-list">
                                    <?php if (!empty($invoice['contact_name'])): ?><span><?= e($invoice['contact_name']); ?></span><?php endif; ?>
                                    <?php if (!empty($invoice['client_phone'])): ?><span><?= e($invoice['client_phone']); ?></span><?php endif; ?>
                                    <?php if (!empty($invoice['client_email'])): ?><span><?= e($invoice['client_email']); ?></span><?php endif; ?>
                                    <?php if (!empty($invoice['client_address'])): ?><span><?= e($invoice['client_address']); ?></span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="document-section-label">Informations</div>
                            <div class="document-party-card">
                                <dl class="document-info-list mb-0">
                                    <div>
                                        <dt>Échéance</dt>
                                        <dd><?= e($invoice['due_date'] ? date('d/m/Y', strtotime((string) $invoice['due_date'])) : '—'); ?></dd>
                                    </div>
                                    <div>
                                        <dt>Créée par</dt>
                                        <dd><?= e($invoice['user_name']); ?></dd>
                                    </div>
                                    <div>
                                        <dt>Montant payé</dt>
                                        <dd><?= e(number_format((float) $invoice['amount_paid'], 2, ',', ' ')); ?></dd>
                                    </div>
                                    <div>
                                        <dt>Solde restant</dt>
                                        <dd class="text-danger fw-semibold"><?= e(number_format((float) $invoice['balance_due'], 2, ',', ' ')); ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table document-lines-table align-middle mb-0">
                            <thead>
                                <tr><th>Description</th><th>Type</th><th>Qté</th><th>Prix</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="table-cell-stack">
                                                <div class="table-cell-main"><?= e($item['description']); ?></div>
                                                <div class="table-cell-meta"><?= e($item['item_type'] === 'product' ? 'Produit stocké' : 'Service'); ?></div>
                                            </div>
                                        </td>
                                        <td><?= e($item['item_type']); ?></td>
                                        <td><?= e(number_format((float) $item['quantity'], 2, ',', ' ')); ?></td>
                                        <td><?= e(number_format((float) $item['unit_price'], 2, ',', ' ')); ?></td>
                                        <td class="fw-semibold"><?= e(number_format((float) $item['line_total'], 2, ',', ' ')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm document-card mb-4">
                <div class="card-body p-4">
                    <div class="document-section-label">Récapitulatif</div>
                    <div class="document-summary-grid">
                        <div><span>Sous-total</span><strong><?= e(number_format((float) $invoice['subtotal'], 2, ',', ' ')); ?></strong></div>
                        <div><span>Remise</span><strong><?= e(number_format((float) $invoice['discount_amount'], 2, ',', ' ')); ?></strong></div>
                        <div><span><?= e(tax_rate_label($invoice['tax_rate'] ?? 0)); ?></span><strong><?= e(number_format((float) $invoice['tax_amount'], 2, ',', ' ')); ?></strong></div>
                        <div class="highlight"><span>Total TTC</span><strong><?= e(number_format((float) $invoice['grand_total'], 2, ',', ' ')); ?></strong></div>
                    </div>
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="document-note mt-4">
                            <div class="document-section-label mb-2">Note interne</div>
                            <p class="mb-0"><?= e($invoice['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm document-card">
                <div class="card-body p-4">
                    <div class="document-section-label">Encaissement</div>
                    <?php if (in_array($invoice['status'], ['validated', 'partial_paid'], true) && (float) $invoice['balance_due'] > 0): ?>
                        <form method="post" action="<?= e(url('/payments/store')); ?>" class="row g-3">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="invoice_id" value="<?= e((string) $invoice['id']); ?>">
                            <div class="col-12"><label class="form-label">Date paiement</label><input type="date" class="form-control" name="payment_date" value="<?= e(date('Y-m-d')); ?>"></div>
                            <div class="col-12"><label class="form-label">Montant</label><input type="number" step="0.01" min="0.01" max="<?= e((string) $invoice['balance_due']); ?>" class="form-control" name="amount" value="<?= e((string) $invoice['balance_due']); ?>" required></div>
                            <div class="col-12"><label class="form-label">Méthode</label><select class="form-select" name="method"><option value="cash">Cash</option><option value="mobile_money">Mobile Money</option><option value="bank_transfer">Virement</option><option value="card">Carte</option><option value="cheque">Chèque</option><option value="other">Autre</option></select></div>
                            <div class="col-12"><label class="form-label">Référence</label><input class="form-control" name="reference"></div>
                            <div class="col-12"><label class="form-label">Note</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                            <div class="col-12 d-grid"><button type="submit" class="btn btn-primary">Encaisser maintenant</button></div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-light mb-0">Aucun paiement supplémentaire possible sur cette facture.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm document-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <div class="document-section-label mb-1">Historique des paiements</div>
                    <p class="text-muted mb-0">Tous les encaissements enregistrés sur cette facture.</p>
                </div>
            </div>
            <?php if ($payments === []): ?>
                <div class="alert alert-light mb-0">Aucun paiement enregistré.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table document-lines-table align-middle mb-0">
                        <thead><tr><th>Numéro</th><th>Date</th><th>Montant</th><th>Méthode</th><th>Référence</th><th>Agent</th></tr></thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= e($payment['payment_number']); ?></td>
                                    <td><?= e(date('d/m/Y', strtotime((string) $payment['payment_date']))); ?></td>
                                    <td class="fw-semibold"><?= e(number_format((float) $payment['amount'], 2, ',', ' ')); ?></td>
                                    <td><span class="badge <?= e(payment_method_badge_class($payment['method'])); ?>"><?= e(payment_method_label($payment['method'])); ?></span></td>
                                    <td><?= e($payment['reference'] ?: '—'); ?></td>
                                    <td><?= e($payment['user_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
