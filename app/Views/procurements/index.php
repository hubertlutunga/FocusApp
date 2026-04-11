<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Approvisionnements</h3>
            <p class="text-muted mb-0">Achats fournisseurs avec réception et impact stock.</p>
        </div>
        <a href="<?= e(url('/procurements/create')); ?>" class="btn btn-primary">Nouvel approvisionnement</a>
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
                                <div class="table-actions">
                                    <a href="<?= e(url('/procurements/show?id=' . $procurement['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Voir</a>
                                    <?php if (user_is_admin() && $procurement['status'] !== 'received' && $procurement['status'] !== 'cancelled'): ?>
                                        <form method="post" action="<?= e(url('/procurements/cancel')); ?>" onsubmit="return confirm('Annuler cet approvisionnement ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $procurement['id']); ?>">
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
