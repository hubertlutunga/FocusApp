<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Unités de mesure</h3>
            <p class="text-muted mb-0">Pièce, forfait, heure, paquet et autres unités métier.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(url('/categories')); ?>" class="btn btn-outline-secondary">Voir les catégories</a>
            <a href="<?= e(url('/units/create')); ?>" class="btn btn-primary">Nouvelle unité</a>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Symbole</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $unit): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($unit['name']); ?></td>
                            <td><span class="badge <?= e(unit_badge_class()); ?>"><?= e($unit['symbol']); ?></span></td>
                            <td class="text-end">
                                <?php if (user_is_admin()): ?>
                                    <div class="table-actions">
                                        <a href="<?= e(url('/units/edit?id=' . $unit['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                        <form method="post" action="<?= e(url('/units/delete')); ?>" onsubmit="return confirm('Archiver cette unité ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $unit['id']); ?>">
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
