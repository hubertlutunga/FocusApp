<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Boutiques / extensions</h3>
            <p class="text-muted mb-0">Créez une ou plusieurs extensions et rattachez-y les caissiers ou gestionnaires de stock.</p>
        </div>
        <a href="<?= e(url('/shops/create')); ?>" class="btn btn-primary">Nouvelle boutique</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Boutique</th>
                        <th data-mobile-hidden="true">Responsable</th>
                        <th>Utilisateurs</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shops as $shop): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($shop['name']); ?></div>
                                    <div class="table-cell-meta"><?= e($shop['code']); ?><?= $shop['city'] ? ' • ' . e($shop['city']) : ''; ?></div>
                                    <?php if (!empty($shop['address'])): ?><div class="table-cell-meta"><?= e($shop['address']); ?></div><?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($shop['manager_name'] ?: '—'); ?></div>
                                    <div class="table-cell-meta"><?= e($shop['phone'] ?: 'Sans téléphone'); ?></div>
                                </div>
                            </td>
                            <td><?= e((string) $shop['user_count']); ?></td>
                            <td><span class="badge <?= e(status_badge_class((int) $shop['is_active'] === 1 ? 'active' : 'inactive')); ?>"><?= (int) $shop['is_active'] === 1 ? 'Active' : 'Inactive'; ?></span></td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(url('/shops/edit?id=' . $shop['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                    <form method="post" action="<?= e(url('/shops/delete')); ?>" onsubmit="return confirm('Archiver cette boutique ?');">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?= e((string) $shop['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Archiver</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>