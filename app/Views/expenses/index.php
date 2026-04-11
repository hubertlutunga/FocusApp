<?php
$expenseCount = count($expenses);
$totalExpenses = array_reduce($expenses, static fn ($carry, $expense) => $carry + (float) $expense['amount'], 0.0);
$outstandingExpenses = array_reduce($expenses, static fn ($carry, $expense) => $carry + (float) ($expense['balance_due'] ?? 0), 0.0);
$currentMonth = date('Y-m');
$monthlyExpenses = array_reduce($expenses, static function ($carry, $expense) use ($currentMonth) {
    return $carry + (str_starts_with((string) $expense['expense_date'], $currentMonth) ? (float) $expense['amount'] : 0.0);
}, 0.0);
?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1">Dépenses</h1>
        <p class="text-muted mb-0">Suivez les charges opérationnelles de Focus Group.</p>
    </div>
    <a href="<?= e(url('/expenses/create')) ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nouvelle dépense
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon primary"><i class="bi bi-receipt"></i></span>
                <div>
                    <div class="muted-label">Total des dépenses</div>
                    <div class="h4 mb-0 text-amount"><?= e(number_format($totalExpenses, 2, ',', ' ')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon warning"><i class="bi bi-calendar-month"></i></span>
                <div>
                    <div class="muted-label">Ce mois-ci</div>
                    <div class="h4 mb-0 text-amount"><?= e(number_format($monthlyExpenses, 2, ',', ' ')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon danger"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <div class="muted-label">Dettes en cours</div>
                    <div class="h4 mb-0 text-amount"><?= e(number_format($outstandingExpenses, 2, ',', ' ')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon success"><i class="bi bi-list-check"></i></span>
                <div>
                    <div class="muted-label">Lignes enregistrées</div>
                    <div class="h4 mb-0"><?= e((string) $expenseCount) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-toolbar">
            <span class="summary-chip"><i class="bi bi-funnel"></i> Recherche, tri et pagination disponibles</span>
            <span class="muted-label"><?= e((string) $expenseCount) ?> dépense(s) affichée(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle js-datatable">
                <thead>
                    <tr>
                        <th>Dépense</th>
                        <th data-mobile-hidden="true">Catégorie</th>
                        <th data-mobile-hidden="true">Statut</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expenses === []): ?>
                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="empty-state">
                                    <i class="bi bi-wallet2"></i>
                                    <div class="fw-semibold mb-1">Aucune dépense enregistrée</div>
                                    <p class="mb-3">Commencez par créer une première dépense pour alimenter vos rapports.</p>
                                    <a href="<?= e(url('/expenses/create')) ?>" class="btn btn-primary btn-sm">Créer une dépense</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td>
                                    <div class="table-cell-stack">
                                        <div class="table-cell-main"><?= e($expense['expense_number']) ?></div>
                                        <div class="table-cell-meta"><?= e(date('d/m/Y', strtotime($expense['expense_date']))) ?></div>
                                        <div class="table-cell-meta"><?= e($expense['description']) ?></div>
                                        <div class="table-cell-meta"><?= e($expense['supplier_name'] ?? 'Sans tiers') ?> • <?= e(payment_method_label($expense['payment_method'])) ?></div>
                                        <div class="table-cell-meta">Payé : <?= e(number_format((float) ($expense['amount_paid'] ?? 0), 2, ',', ' ')) ?> • Solde : <?= e(number_format((float) ($expense['balance_due'] ?? 0), 2, ',', ' ')) ?></div>
                                    </div>
                                </td>
                                <td><?= e($expense['category_name']) ?></td>
                                <td><span class="badge <?= e(status_badge_class($expense['payment_status'] ?? 'paid')) ?>"><?= e(status_label($expense['payment_status'] ?? 'paid')) ?></span></td>
                                <td class="text-end fw-semibold text-amount"><?= e(number_format((float) $expense['amount'], 2, ',', ' ')) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= e(url('/expenses/show?id=' . (int) $expense['id'])) ?>" class="btn btn-sm btn-outline-primary table-action-btn">Voir</a>
                                        <a href="<?= e(url('/expenses/edit?id=' . (int) $expense['id'])) ?>" class="btn btn-sm btn-outline-primary table-action-btn">Modifier</a>
                                        <?php if (user_is_admin()): ?>
                                            <form action="<?= e(url('/expenses/delete')) ?>" method="POST" onsubmit="return confirm('Archiver cette dépense ?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= e((string) $expense['id']) ?>">
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
