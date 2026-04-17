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