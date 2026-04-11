<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Utilisateurs</h3>
            <p class="text-muted mb-0">Créez et gérez les comptes d’accès à l’application.</p>
        </div>
        <a href="<?= e(url('/users/create')); ?>" class="btn btn-primary">Nouvel utilisateur</a>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Profil</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($user['full_name']); ?></div>
                                    <div class="table-cell-meta"><?= e($user['email']); ?></div>
                                    <div class="table-cell-meta"><?= e($user['phone'] ?: 'Sans téléphone'); ?><?= $user['last_login_at'] ? ' • Dernière connexion : ' . e(date('d/m/Y H:i', strtotime((string) $user['last_login_at']))) : ''; ?></div>
                                </div>
                            </td>
                            <td><span class="badge <?= e(module_badge_class($user['role_code'] === 'caisse' ? 'paiements' : ($user['role_code'] === 'gestionnaire_stock' ? 'stock' : 'authentification'))); ?>"><?= e($user['role_name']); ?></span></td>
                            <td><span class="badge <?= e(status_badge_class((int) $user['is_active'] === 1 ? 'active' : 'inactive')); ?>"><?= (int) $user['is_active'] === 1 ? 'Actif' : 'Inactif'; ?></span></td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(url('/users/edit?id=' . $user['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                    <?php if ((int) (\App\Core\Auth::id() ?? 0) !== (int) $user['id']): ?>
                                        <form method="post" action="<?= e(url('/users/delete')); ?>" onsubmit="return confirm('Archiver cet utilisateur ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $user['id']); ?>">
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
    </div>
</div>