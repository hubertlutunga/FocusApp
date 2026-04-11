<?php $logCount = count($logs); ?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1">Journal d’activité</h1>
        <p class="text-muted mb-0">Consultez les opérations réalisées dans l’application.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 filter-card">
    <div class="card-body">
        <form method="GET" action="<?= e(url('/activity-logs')) ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="module" class="form-label">Module</label>
                <select name="module" id="module" class="form-select">
                    <option value="">Tous les modules</option>
                    <?php foreach ($modules as $module): ?>
                        <option value="<?= e($module) ?>" <?= $selectedModule === $module ? 'selected' : '' ?>><?= e(ucfirst($module)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="action" class="form-label">Action</label>
                <select name="action" id="action" class="form-select">
                    <option value="">Toutes les actions</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?= e($action) ?>" <?= $selectedAction === $action ? 'selected' : '' ?>><?= e(ucfirst($action)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= e(url('/activity-logs')) ?>" class="btn btn-light">Réinitialiser</a>
            </div>
        </form>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <span class="summary-chip"><i class="bi bi-clock-history"></i> <?= e((string) $logCount) ?> entrée(s)</span>
            <?php if ($selectedModule !== ''): ?>
                <span class="summary-chip"><i class="bi bi-grid"></i> Module: <?= e(module_label($selectedModule)) ?></span>
            <?php endif; ?>
            <?php if ($selectedAction !== ''): ?>
                <span class="summary-chip"><i class="bi bi-lightning-charge"></i> Action: <?= e(ucfirst($selectedAction)) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Activité</th>
                        <th>Action</th>
                        <th>Détail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs === []): ?>
                        <tr>
                            <td colspan="3" class="p-0">
                                <div class="empty-state">
                                    <i class="bi bi-journal-text"></i>
                                    <div class="fw-semibold mb-1">Aucune activité à afficher</div>
                                    <p class="mb-0">Essayez d’élargir les filtres ou effectuez une première opération dans l’application.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($log['user_name'] ?? 'Système') ?></div>
                                        <div class="table-cell-meta"><?= e(date('d/m/Y H:i', strtotime($log['created_at']))) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-cell-stack">
                                        <div><span class="badge badge-action <?= e(strtolower((string) $log['action'])) ?>"><?= e($log['action']) ?></span></div>
                                        <div class="table-cell-meta"><?= e(module_label($log['module'])) ?></div>
                                    </div>
                                </td>
                                <td><?= e($log['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
