<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Répertoire fournisseurs</h3>
            <p class="text-muted mb-0">Gestion des partenaires et sources d’approvisionnement.</p>
        </div>
        <a href="<?= e(url('/suppliers/create')); ?>" class="btn btn-primary">Nouveau fournisseur</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th data-mobile-hidden="true">Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($supplier['company_name']); ?></div>
                                    <div class="table-cell-meta"><?= e($supplier['supplier_code']); ?></div>
                                    <?php if (!empty($supplier['contact_name'])): ?>
                                        <div class="table-cell-meta"><?= e($supplier['contact_name']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($supplier['phone']) || !empty($supplier['email']) || !empty($supplier['city'])): ?>
                                        <div class="table-cell-meta">
                                            <?= e($supplier['phone'] ?: '—'); ?><?= $supplier['email'] ? ' • ' . e($supplier['email']) : ''; ?><?= $supplier['city'] ? ' • ' . e($supplier['city']) : ''; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= (int) $supplier['is_active'] === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?= (int) $supplier['is_active'] === 1 ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if (user_is_admin()): ?>
                                    <div class="table-actions">
                                        <a href="<?= e(url('/suppliers/edit?id=' . $supplier['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                        <form method="post" action="<?= e(url('/suppliers/delete')); ?>" onsubmit="return confirm('Archiver ce fournisseur ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $supplier['id']); ?>">
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
