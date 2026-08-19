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

<?php if ($isShopUser): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h3 class="h5 mb-1">Envoyer du stock au siège</h3>
        <p class="text-muted mb-0">Sélectionnez un ou plusieurs produits de votre boutique pour les envoyer au stock général.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e(url('/stock/return')); ?>" class="row g-3 align-items-end stock-multi-form" data-row-template="sendRowTemplate" data-row-container="sendRows">
            <?= csrf_field(); ?>
            <div class="col-12">
                <label class="form-label" for="send_note">Note</label>
                <input class="form-control" id="send_note" name="note" placeholder="Motif de l'envoi">
            </div>
            <div class="col-12">
                <div class="stock-transfer-rows" id="sendRows">
                    <div class="row g-2 align-items-end stock-transfer-row">
                        <div class="col-md-7">
                            <label class="form-label">Produit</label>
                            <select class="form-select js-product-select js-select2" name="items[product_id][]" data-placeholder="Rechercher et sélectionner un produit" required>
                                <option value="">Sélectionner</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= e((string) $product['id']); ?>"><?= e($product['name'] . ' (' . $product['sku'] . ') — Stock boutique : ' . number_format((float) $product['current_stock'], 2, ',', ' ')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="items[quantity][]" required>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" class="btn btn-outline-danger js-remove-stock-row" disabled>Retirer</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary js-add-stock-row">Ajouter un autre produit</button>
                <button type="submit" class="btn btn-primary">Envoyer du stock</button>
            </div>
        </form>
    </div>
</div>

<template id="sendRowTemplate">
    <div class="row g-2 align-items-end stock-transfer-row mt-2">
        <div class="col-md-7">
            <label class="form-label">Produit</label>
            <select class="form-select js-product-select js-select2" name="items[product_id][]" data-placeholder="Rechercher et sélectionner un produit" required>
                <option value="">Sélectionner</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= e((string) $product['id']); ?>"><?= e($product['name'] . ' (' . $product['sku'] . ') — Stock boutique : ' . number_format((float) $product['current_stock'], 2, ',', ' ')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantité</label>
            <input type="number" step="0.01" min="0.01" class="form-control" name="items[quantity][]" required>
        </div>
        <div class="col-md-2 d-grid">
            <button type="button" class="btn btn-outline-danger js-remove-stock-row">Retirer</button>
        </div>
    </div>
</template>
<?php endif; ?>

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
