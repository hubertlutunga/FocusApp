<?php
$currentUser = \App\Core\Auth::user();
$currentPath = current_path();

$matchesGroup = static function (array $paths) use ($currentPath): bool {
    foreach ($paths as $path) {
        $normalized = '/' . trim($path, '/');
        if ($normalized === '//' || $normalized === '') {
            $normalized = '/';
        }

        if ($currentPath === $normalized) {
            return true;
        }
    }

    return false;
};

$adminPaths = ['/users', '/users/create', '/users/edit', '/settings/company', '/activity-logs'];
$referencePaths = ['/clients', '/clients/create', '/clients/edit', '/suppliers', '/suppliers/create', '/suppliers/edit', '/categories', '/categories/create', '/categories/edit', '/units', '/units/create', '/units/edit'];
$stockPaths = ['/products', '/products/create', '/products/edit', '/services', '/services/create', '/services/edit', '/stock', '/procurements', '/procurements/create', '/procurements/show'];
$commercialPaths = ['/quotes', '/quotes/create', '/quotes/show', '/invoices', '/invoices/create', '/invoices/show', '/payments', '/payments/create'];
$pilotagePaths = ['/expenses', '/expenses/create', '/expenses/show', '/expenses/edit', '/reports'];

$adminOpen = $matchesGroup($adminPaths);
$referenceOpen = $matchesGroup($referencePaths);
$stockOpen = $matchesGroup($stockPaths);
$commercialOpen = $matchesGroup($commercialPaths);
$pilotageOpen = $matchesGroup($pilotagePaths);

$isAdmin = user_is_admin();
$canAccessCaisse = user_can_access_caisse();
$canAccessCommercial = user_can_access_commercial();
$canAccessStock = user_can_access_stock_management();
?>
<aside class="sidebar border-end" id="appSidebar">
    <div class="sidebar-brand">
        <a href="<?= e(url('/dashboard')); ?>" class="sidebar-brand-link" aria-label="Retour au tableau de bord">
            <div class="sidebar-brand-card">
                <img src="<?= e(project_asset('images/logo_focusprojet_Blanc.png')); ?>" alt="Focus Group" class="sidebar-logo">
            </div>
        </a>
    </div>

    <nav class="nav flex-column sidebar-nav">
        <a class="nav-link <?= is_active_path(['/','/dashboard']); ?>" href="<?= e(url('/dashboard')); ?>"><i class="bi bi-grid-1x2-fill"></i><span>Tableau de bord</span></a>

        <?php if ($isAdmin): ?>
        <div class="sidebar-group">
            <button class="nav-link sidebar-group-toggle <?= $adminOpen ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-admin" aria-expanded="<?= $adminOpen ? 'true' : 'false' ?>" aria-controls="sidebar-admin">
                <span class="d-flex align-items-center gap-3"><i class="bi bi-sliders"></i><span>Administration</span></span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </button>
            <div class="collapse <?= $adminOpen ? 'show' : '' ?>" id="sidebar-admin">
                <div class="sidebar-subnav">
                    <a class="nav-link <?= is_active_path(['/users','/users/create','/users/edit']); ?>" href="<?= e(url('/users')); ?>"><i class="bi bi-people"></i><span>Utilisateurs</span></a>
                    <a class="nav-link <?= is_active_path(['/settings/company']); ?>" href="<?= e(url('/settings/company')); ?>"><i class="bi bi-building-gear"></i><span>Infos entreprise</span></a>
                    <a class="nav-link <?= is_active_path(['/activity-logs']); ?>" href="<?= e(url('/activity-logs')); ?>"><i class="bi bi-journal-text"></i><span>Journal d’activité</span></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canAccessCaisse || $canAccessStock || $isAdmin): ?>
        <div class="sidebar-group">
            <button class="nav-link sidebar-group-toggle <?= $referenceOpen ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-reference" aria-expanded="<?= $referenceOpen ? 'true' : 'false' ?>" aria-controls="sidebar-reference">
                <span class="d-flex align-items-center gap-3"><i class="bi bi-diagram-3"></i><span>Référentiels</span></span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </button>
            <div class="collapse <?= $referenceOpen ? 'show' : '' ?>" id="sidebar-reference">
                <div class="sidebar-subnav">
                    <?php if ($canAccessCaisse || $isAdmin): ?>
                    <a class="nav-link <?= is_active_path(['/clients','/clients/create','/clients/edit']); ?>" href="<?= e(url('/clients')); ?>"><i class="bi bi-person-badge"></i><span>Clients</span></a>
                    <?php endif; ?>
                    <?php if ($canAccessStock || $isAdmin): ?>
                    <a class="nav-link <?= is_active_path(['/suppliers','/suppliers/create','/suppliers/edit']); ?>" href="<?= e(url('/suppliers')); ?>"><i class="bi bi-truck"></i><span>Fournisseurs</span></a>
                    <a class="nav-link <?= is_active_path(['/categories','/categories/create','/categories/edit','/units','/units/create','/units/edit']); ?>" href="<?= e(url('/categories')); ?>"><i class="bi bi-tags"></i><span>Catégories & unités</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canAccessStock || $isAdmin): ?>
        <div class="sidebar-group">
            <button class="nav-link sidebar-group-toggle <?= $stockOpen ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-stock" aria-expanded="<?= $stockOpen ? 'true' : 'false' ?>" aria-controls="sidebar-stock">
                <span class="d-flex align-items-center gap-3"><i class="bi bi-box-seam"></i><span>Catalogue</span></span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </button>
            <div class="collapse <?= $stockOpen ? 'show' : '' ?>" id="sidebar-stock">
                <div class="sidebar-subnav">
                    <a class="nav-link <?= is_active_path(['/products','/products/create','/products/edit']); ?>" href="<?= e(url('/products')); ?>"><i class="bi bi-box-seam"></i><span>Produits</span></a>
                    <a class="nav-link <?= is_active_path(['/services','/services/create','/services/edit']); ?>" href="<?= e(url('/services')); ?>"><i class="bi bi-tools"></i><span>Services</span></a>
                    <a class="nav-link <?= is_active_path(['/stock']); ?>" href="<?= e(url('/stock')); ?>"><i class="bi bi-boxes"></i><span>Stock & mouvements</span></a>
                    <a class="nav-link <?= is_active_path(['/procurements','/procurements/create','/procurements/show']); ?>" href="<?= e(url('/procurements')); ?>"><i class="bi bi-cart-check"></i><span>Approvisionnements</span></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canAccessCommercial || $isAdmin): ?>
        <div class="sidebar-group">
            <button class="nav-link sidebar-group-toggle <?= $commercialOpen ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-commercial" aria-expanded="<?= $commercialOpen ? 'true' : 'false' ?>" aria-controls="sidebar-commercial">
                <span class="d-flex align-items-center gap-3"><i class="bi bi-currency-exchange"></i><span>Commercial</span></span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </button>
            <div class="collapse <?= $commercialOpen ? 'show' : '' ?>" id="sidebar-commercial">
                <div class="sidebar-subnav">
                    <a class="nav-link <?= is_active_path(['/quotes','/quotes/create','/quotes/show']); ?>" href="<?= e(url('/quotes')); ?>"><i class="bi bi-file-earmark-text"></i><span>Devis</span></a>
                    <a class="nav-link <?= is_active_path(['/invoices','/invoices/create','/invoices/show']); ?>" href="<?= e(url('/invoices')); ?>"><i class="bi bi-receipt-cutoff"></i><span>Factures</span></a>
                    <a class="nav-link <?= is_active_path(['/payments','/payments/create']); ?>" href="<?= e(url('/payments')); ?>"><i class="bi bi-cash-stack"></i><span>Paiements</span></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canAccessCaisse || $isAdmin): ?>
        <div class="sidebar-group">
            <button class="nav-link sidebar-group-toggle <?= $pilotageOpen ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-pilotage" aria-expanded="<?= $pilotageOpen ? 'true' : 'false' ?>" aria-controls="sidebar-pilotage">
                <span class="d-flex align-items-center gap-3"><i class="bi bi-graph-up-arrow"></i><span>Pilotage</span></span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </button>
            <div class="collapse <?= $pilotageOpen ? 'show' : '' ?>" id="sidebar-pilotage">
                <div class="sidebar-subnav">
                    <a class="nav-link <?= is_active_path(['/expenses','/expenses/create','/expenses/show','/expenses/edit']); ?>" href="<?= e(url('/expenses')); ?>"><i class="bi bi-wallet2"></i><span>Dépenses</span></a>
                    <a class="nav-link <?= is_active_path(['/reports']); ?>" href="<?= e(url('/reports')); ?>"><i class="bi bi-bar-chart-line"></i><span>Rapports</span></a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user mt-auto">
        <div class="sidebar-user-name"><?= e($currentUser['full_name'] ?? 'Invité'); ?></div>
        <small><?= e($currentUser['role_name'] ?? ''); ?></small>
    </div>
</aside>
