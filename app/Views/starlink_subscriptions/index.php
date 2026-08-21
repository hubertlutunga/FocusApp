<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Abonnements Starlink</h3>
            <p class="text-muted mb-0">Suivi des lignes clients et échéances d'abonnement.</p>
        </div>
        <a href="<?= e(url('/starlink-subscriptions/create')); ?>" class="btn btn-primary">Nouvel abonnement</a>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if ($subscriptions === []): ?>
            <div class="empty-state">
                <i class="bi bi-router"></i>
                <div class="fw-semibold mb-1">Aucun abonnement enregistré</div>
                <p class="mb-0">Ajoutez votre premier abonnement Starlink client.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle js-datatable">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Ligne</th>
                            <th data-mobile-hidden="true">Plan</th>
                            <th>Échéance</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $subscription): ?>
                            <?php
                            $days = (int) ($subscription['days_to_expiry'] ?? 0);
                            $status = (string) $subscription['status'];
                            $badgeClass = 'badge-status badge-status-default';
                            if ($status === 'active' && $days < 0) {
                                $badgeClass = 'badge-status badge-status-danger';
                            } elseif ($status === 'active' && $days <= 7) {
                                $badgeClass = 'badge-status badge-status-warning';
                            } elseif ($status === 'active') {
                                $badgeClass = 'badge-status badge-status-success';
                            } elseif ($status === 'expired') {
                                $badgeClass = 'badge-status badge-status-danger';
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($subscription['company_name']); ?></div>
                                        <div class="table-cell-meta"><?= e($subscription['client_code']); ?></div>
                                        <?php if (!empty($subscription['contact_name']) || !empty($subscription['phone'])): ?>
                                            <div class="table-cell-meta"><?= e($subscription['contact_name'] ?: '—'); ?><?= !empty($subscription['phone']) ? ' • ' . e($subscription['phone']) : ''; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($subscription['line_label']); ?></div>
                                        <div class="table-cell-meta"><?= e($subscription['subscription_number'] ?: 'Sans numéro'); ?></div>
                                    </div>
                                </td>
                                <td><?= e($subscription['plan_name'] ?: '—'); ?></td>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e(date('d/m/Y', strtotime((string) $subscription['end_date']))); ?></div>
                                        <div class="table-cell-meta">
                                            <?php if ($days < 0): ?>
                                                Expiré depuis <?= e((string) abs($days)); ?> jour(s)
                                            <?php elseif ($days === 0): ?>
                                                Échéance aujourd'hui
                                            <?php else: ?>
                                                Dans <?= e((string) $days); ?> jour(s)
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge <?= e($badgeClass); ?>"><?= e(status_label($status)); ?></span></td>
                                <td class="text-end">
                                    <div class="table-actions">
                                        <a href="<?= e(url('/starlink-subscriptions/edit?id=' . $subscription['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                        <?php if (user_is_admin()): ?>
                                            <form method="post" action="<?= e(url('/starlink-subscriptions/delete')); ?>" onsubmit="return confirm('Archiver cet abonnement ?');">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?= e((string) $subscription['id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Archiver</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
