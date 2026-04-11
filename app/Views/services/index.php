<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Catalogue services</h3>
            <p class="text-muted mb-0">Prestations techniques commercialisées par Focus Group.</p>
        </div>
        <a href="<?= e(url('/services/create')); ?>" class="btn btn-primary">Nouveau service</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Prix</th>
                        <th data-mobile-hidden="true">Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($service['name']); ?></div>
                                    <div class="table-cell-meta"><?= e($service['code']); ?> • <?= e($service['category_name']); ?></div>
                                    <div class="table-cell-meta"><?= e($service['description'] ?: 'Sans description'); ?></div>
                                    <?php if ($service['duration_hours'] !== null): ?>
                                        <div class="table-cell-meta">Durée : <?= e(number_format((float) $service['duration_hours'], 2, ',', ' ') . ' h'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e(number_format((float) $service['unit_price'], 2, ',', ' ')); ?></div>
                                    <div class="table-cell-meta">Coût : <?= e(number_format((float) $service['estimated_cost'], 2, ',', ' ')); ?></div>
                                </div>
                            </td>
                            <td><span class="badge rounded-pill <?= (int) $service['is_active'] === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?= (int) $service['is_active'] === 1 ? 'Actif' : 'Inactif'; ?></span></td>
                            <td class="text-end">
                                <?php if (user_is_admin()): ?>
                                    <div class="table-actions">
                                        <a href="<?= e(url('/services/edit?id=' . $service['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                        <form method="post" action="<?= e(url('/services/delete')); ?>" onsubmit="return confirm('Archiver ce service ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $service['id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Archiver</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Lecture seule</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
