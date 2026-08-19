<?php
$isShopUser = $currentShopId !== null;
?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1">Réceptions de stock</h1>
        <p class="text-muted mb-0">
            <?= $isShopUser
                ? 'Validez les transferts envoyés vers votre boutique avant qu\'ils ne deviennent effectifs.'
                : 'Validez les retours de stock envoyés par les boutiques vers le stock général.'; ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($canManageCentral): ?>
            <a href="<?= e(url('/stock')); ?>" class="btn btn-primary"><i class="bi bi-send me-1"></i> Envoyer du stock</a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="h5 mb-1">Transferts en attente de réception</h3>
            <p class="text-muted mb-0">Cliquez sur Reçu pour appliquer les mouvements de stock.</p>
        </div>
        <span class="badge text-bg-warning"><?= e((string) count($pendingTransfers)); ?> en attente</span>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if ($pendingTransfers === []): ?>
            <div class="empty-state py-4">
                <i class="bi bi-bell"></i>
                <div>Aucun transfert à réceptionner pour le moment.</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Mouvement</th>
                            <th>Qté</th>
                            <th>Date demande</th>
                            <th>Créé par</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingTransfers as $transfer): ?>
                            <?php $isReturn = ($transfer['transfer_type'] ?? 'to_shop') === 'to_central'; ?>
                            <tr>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($transfer['product_name']); ?></div>
                                        <div class="table-cell-meta"><?= e($transfer['sku']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= $isReturn ? 'Retour vers stock général' : 'Transfert vers boutique'; ?></div>
                                        <div class="table-cell-meta">
                                            <?= e($isReturn
                                                ? ($transfer['source_shop_name'] ?: 'Boutique')
                                                : ($transfer['destination_shop_name'] ?: ($currentShopName ?: 'Boutique'))); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-semibold"><?= e(number_format((float) $transfer['quantity'], 2, ',', ' ')); ?></td>
                                <td><?= e(date('d/m/Y H:i', strtotime((string) ($transfer['requested_at'] ?: $transfer['created_at'])))); ?></td>
                                <td><?= e($transfer['user_name'] ?: '—'); ?></td>
                                <td class="text-end">
                                    <form method="post" action="<?= e(url('/stock/transfers/receive')); ?>" class="d-inline-block">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="transfer_id" value="<?= e((string) $transfer['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Reçu</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h3 class="h5 mb-1">Historique récent</h3>
        <p class="text-muted mb-0">Vue des derniers transferts et retours.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if ($recentTransfers === []): ?>
            <div class="alert alert-light mb-0">Aucun transfert enregistré.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle js-datatable">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Mouvement</th>
                            <th>Qté</th>
                            <th>Statut</th>
                            <th data-mobile-hidden="true">Date</th>
                            <th data-mobile-hidden="true">Réceptionné par</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransfers as $transfer): ?>
                            <?php $isReturn = ($transfer['transfer_type'] ?? 'to_shop') === 'to_central'; ?>
                            <tr>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($transfer['product_name']); ?></div>
                                        <div class="table-cell-meta"><?= e($transfer['sku']); ?></div>
                                    </div>
                                </td>
                                <td><?= e($isReturn ? 'Retour vers stock général' : 'Transfert vers boutique'); ?></td>
                                <td><?= e(number_format((float) $transfer['quantity'], 2, ',', ' ')); ?></td>
                                <td><span class="badge <?= e(status_badge_class($transfer['status'] ?? 'pending')); ?>"><?= e(status_label($transfer['status'] ?? 'pending')); ?></span></td>
                                <td><?= e(date('d/m/Y H:i', strtotime((string) $transfer['created_at']))); ?></td>
                                <td><?= e($transfer['received_by_name'] ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
