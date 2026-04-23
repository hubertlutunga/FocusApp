<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Répertoire clients</h3>
            <p class="text-muted mb-0"><?= !empty($onlyDebtors) ? 'Clients avec factures impayees ou partiellement payees.' : 'Gestion des clients entreprises et particuliers.'; ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($onlyDebtors)): ?>
                <a href="<?= e(url('/clients')); ?>" class="btn btn-outline-secondary">Tous les clients</a>
            <?php endif; ?>
            <a href="<?= e(url('/clients/create')); ?>" class="btn btn-primary">Nouveau client</a>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if (!empty($onlyDebtors)): ?>
            <div class="table-toolbar">
                <span class="summary-chip"><i class="bi bi-hourglass-split"></i> Clients avec dette</span>
                <span class="muted-label"><?= e((string) count($clients)); ?> client(s) concerné(s)</span>
            </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th data-mobile-hidden="true">Statut</th>
                        <th class="text-end">Dette client</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clients === []): ?>
                        <tr>
                            <td colspan="4" class="p-0">
                                <div class="empty-state">
                                    <i class="bi bi-person-vcard"></i>
                                    <div class="fw-semibold mb-1">Aucun client a afficher</div>
                                    <p class="mb-0"><?= !empty($onlyDebtors) ? 'Aucun client n a actuellement de dette ouverte.' : 'Commencez par creer un premier client.'; ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="table-cell-stack">
                                    <div class="table-cell-main"><?= e($client['company_name']); ?></div>
                                    <div class="table-cell-meta"><?= e($client['client_code']); ?><?= $client['tax_number'] ? ' • ' . e($client['tax_number']) : ' • Sans NIF'; ?></div>
                                    <?php if (!empty($client['contact_name'])): ?>
                                        <div class="table-cell-meta"><?= e($client['contact_name']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($client['phone']) || !empty($client['email']) || !empty($client['city'])): ?>
                                        <div class="table-cell-meta">
                                            <?= e($client['phone'] ?: '—'); ?><?= $client['email'] ? ' • ' . e($client['email']) : ''; ?><?= $client['city'] ? ' • ' . e($client['city']) : ''; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= (int) $client['is_active'] === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?= (int) $client['is_active'] === 1 ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="table-cell-stack align-items-end">
                                    <div class="table-cell-main text-amount <?= (float) ($client['outstanding_balance'] ?? 0) > 0 ? 'text-danger' : ''; ?>">
                                        <?= e(number_format((float) ($client['outstanding_balance'] ?? 0), 2, ',', ' ')); ?>
                                    </div>
                                    <div class="table-cell-meta">
                                        <?= e((string) ((int) ($client['outstanding_invoice_count'] ?? 0))); ?> facture(s) ouverte(s)
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(url('/clients/edit?id=' . $client['id'])); ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                    <?php if (user_is_admin()): ?>
                                        <form method="post" action="<?= e(url('/clients/delete')); ?>" onsubmit="return confirm('Archiver ce client ?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $client['id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger table-action-btn">Archiver</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
