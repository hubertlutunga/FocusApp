<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Catégories</h3>
            <p class="text-muted mb-0">Classification des produits, services ou usages mixtes.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(url('/units')); ?>" class="btn btn-outline-secondary">Voir les unités</a>
            <a href="<?= e(url('/categories/create')); ?>" class="btn btn-primary">Nouvelle catégorie</a>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Nom</th>
                        <th data-mobile-hidden="true">Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><span class="badge <?= e(category_type_badge_class($category['type'])); ?>"><?= e(category_type_label($category['type'])); ?></span></td>
                            <td class="fw-semibold"><?= e($category['name']); ?></td>
                            <td><?= e($category['description'] ?: '—'); ?></td>
                            <td class="text-end">
                                <?php if (user_is_admin()): ?>
                                    <div class="table-actions">
                                        <a href="<?= e(url('/categories/edit?id=' . $category['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                        <form method="post" action="<?= e(url('/categories/delete')); ?>" onsubmit="return confirm('Archiver cette catégorie ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $category['id']); ?>">
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
