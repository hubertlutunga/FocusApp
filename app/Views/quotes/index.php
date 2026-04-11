<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Devis</h3>
            <p class="text-muted mb-0">Propositions commerciales mixtes produits et services.</p>
        </div>
        <a href="<?= e(url('/quotes/create')); ?>" class="btn btn-primary">Nouveau devis</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Devis</th>
                        <th data-mobile-hidden="true">Statut</th>
                        <th>Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $quote): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($quote['quote_number']); ?></div>
                                    <div class="table-cell-meta"><?= e($quote['client_name']); ?></div>
                                    <div class="table-cell-meta">Émis le <?= e(date('d/m/Y', strtotime((string) $quote['quote_date']))); ?></div>
                                    <div class="table-cell-meta">Valide jusqu’au <?= e($quote['valid_until'] ? date('d/m/Y', strtotime((string) $quote['valid_until'])) : '—'); ?></div>
                                </div>
                            </td>
                            <td><span class="badge <?= e(status_badge_class($quote['status'])); ?>"><?= e(status_label($quote['status'])); ?></span></td>
                            <td><?= e(number_format((float) $quote['grand_total'], 2, ',', ' ')); ?></td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(url('/quotes/show?id=' . $quote['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Voir</a>
                                    <a href="<?= e(url('/quotes/pdf?id=' . $quote['id'])); ?>" class="btn btn-sm btn-outline-secondary table-action-btn" target="_blank">PDF</a>
                                    <?php if (user_is_admin() && !in_array($quote['status'], ['converted', 'cancelled'], true)): ?>
                                        <form method="post" action="<?= e(url('/quotes/cancel')); ?>" onsubmit="return confirm('Annuler ce devis ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $quote['id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Annuler</button>
                                        </form>
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
