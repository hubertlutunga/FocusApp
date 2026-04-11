<div class="document-shell">
    <div class="document-hero quote-document-hero mb-4">
        <div>
            <div class="document-kicker">Proposition commerciale</div>
            <h1 class="document-title mb-2">Devis <?= e($quote['quote_number']); ?></h1>
            <p class="document-subtitle mb-0">Préparé pour <?= e($quote['client_name']); ?>, valable jusqu’au <?= e($quote['valid_until'] ? date('d/m/Y', strtotime((string) $quote['valid_until'])) : '—'); ?>.</p>
        </div>
        <div class="document-hero-side">
            <span class="badge <?= e(status_badge_class($quote['status'])); ?> document-status-badge"><?= e(status_label($quote['status'])); ?></span>
            <div class="document-total-card">
                <span>Montant proposé</span>
                <strong><?= e(number_format((float) $quote['grand_total'], 2, ',', ' ')); ?></strong>
            </div>
        </div>
    </div>

    <div class="document-toolbar mb-4">
        <a href="<?= e(url('/quotes/pdf?id=' . $quote['id'])); ?>" target="_blank" class="btn btn-outline-secondary">PDF</a>
        <?php if (!in_array($quote['status'], ['converted', 'cancelled'], true)): ?>
            <form method="post" action="<?= e(url('/quotes/convert')); ?>">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= e((string) $quote['id']); ?>">
                <button type="submit" class="btn btn-primary">Convertir en facture</button>
            </form>
        <?php endif; ?>
        <?php if (user_is_admin() && !in_array($quote['status'], ['converted', 'cancelled'], true)): ?>
            <form method="post" action="<?= e(url('/quotes/cancel')); ?>" onsubmit="return confirm('Annuler ce devis ?');">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= e((string) $quote['id']); ?>">
                <button type="submit" class="btn btn-outline-danger">Annuler</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm document-card h-100">
                <div class="card-body p-4">
                    <div class="document-section-label">Synthèse du devis</div>
                    <div class="document-summary-grid compact mb-4">
                        <div><span>Date d’émission</span><strong><?= e(date('d/m/Y', strtotime((string) $quote['quote_date']))); ?></strong></div>
                        <div><span>Validité</span><strong><?= e($quote['valid_until'] ? date('d/m/Y', strtotime((string) $quote['valid_until'])) : '—'); ?></strong></div>
                        <div><span>Sous-total</span><strong><?= e(number_format((float) $quote['subtotal'], 2, ',', ' ')); ?></strong></div>
                        <div class="highlight"><span>Total TTC</span><strong><?= e(number_format((float) $quote['grand_total'], 2, ',', ' ')); ?></strong></div>
                    </div>

                    <div class="document-section-label">Client</div>
                    <div class="document-party-card mb-4">
                        <h3 class="h5 mb-2"><?= e($quote['client_name']); ?></h3>
                        <div class="document-meta-list">
                            <?php if (!empty($quote['contact_name'])): ?><span><?= e($quote['contact_name']); ?></span><?php endif; ?>
                            <?php if (!empty($quote['client_phone'])): ?><span><?= e($quote['client_phone']); ?></span><?php endif; ?>
                            <?php if (!empty($quote['client_email'])): ?><span><?= e($quote['client_email']); ?></span><?php endif; ?>
                            <?php if (!empty($quote['client_address'])): ?><span><?= e($quote['client_address']); ?></span><?php endif; ?>
                        </div>
                    </div>

                    <?php if ($quote['notes']): ?>
                        <div class="document-note">
                            <div class="document-section-label mb-2">Message commercial</div>
                            <p class="mb-0"><?= e($quote['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm document-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <div class="document-section-label mb-1">Lignes du devis</div>
                            <p class="text-muted mb-0">Présentation détaillée des produits et services proposés.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table document-lines-table align-middle mb-0">
                            <thead><tr><th>Description</th><th>Type</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="table-cell-stack">
                                                <div class="table-cell-main"><?= e($item['description']); ?></div>
                                                <div class="table-cell-meta"><?= e($item['item_type'] === 'product' ? 'Produit' : 'Service'); ?></div>
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
    </div>
</div>
