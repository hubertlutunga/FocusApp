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
                    <dt class="col-5">Créé par</dt><dd class="col-7"><?= e($procurement['user_name']); ?></dd>
                    <dt class="col-5">Total</dt><dd class="col-7 fw-semibold"><?= e(number_format((float) $procurement['grand_total'], 2, ',', ' ')); ?></dd>
                </dl>
                <?php if ($procurement['notes']): ?>
                    <hr>
                    <div class="small text-muted"><?= e($procurement['notes']); ?></div>
                <?php endif; ?>
                <div class="d-flex gap-2 mt-4">
                    <?php if ($procurement['status'] !== 'received' && $procurement['status'] !== 'cancelled'): ?>
                        <form method="post" action="<?= e(url('/procurements/receive')); ?>">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= e((string) $procurement['id']); ?>">
                            <button type="submit" class="btn btn-primary">Marquer reçu</button>
                        </form>
                        <?php if (user_is_admin()): ?>
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
        <div class="card border-0 shadow-sm h-100">
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
    </div>
</div>
