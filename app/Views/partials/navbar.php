<?php $currentUser = \App\Core\Auth::user(); ?>
<header class="topbar d-flex align-items-center justify-content-between mb-4">
    <div class="topbar-heading min-w-0">
        <div class="topbar-mobile-bar d-lg-none">
            <button type="button" class="btn topbar-menu-toggle" data-sidebar-toggle aria-controls="appSidebar" aria-expanded="false" aria-label="Ouvrir le menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-mobile-brand-group">
                <a href="<?= e(url('/dashboard')); ?>" class="topbar-mobile-brand" aria-label="Retour au tableau de bord">
                    <img src="<?= e(project_asset('images/logo_focusprojet_bleu.png')); ?>" alt="Focus Group" class="topbar-mobile-logo">
                </a>
                <a href="<?= e(url('/logout')); ?>" class="btn btn-primary topbar-logout topbar-logout-mobile" title="Déconnexion" aria-label="Déconnexion">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
        <h2 class="h4 mb-1 topbar-title"><?= e($pageTitle ?? 'Tableau de bord'); ?></h2>
        <p class="text-muted mb-0 topbar-subtitle"><?= e(app_scope_description()); ?></p>
    </div>
    <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end topbar-actions">
        <div class="topbar-user text-start text-md-end d-none d-md-flex">
            <span class="topbar-user-icon"><i class="bi bi-person-circle"></i></span>
            <div>
                <div class="fw-semibold"><?= e($currentUser['full_name'] ?? ''); ?></div>
                <small class="text-muted"><?= e($currentUser['role_name'] ?? ''); ?></small>
            </div>
        </div>
        <a href="<?= e(url('/logout')); ?>" class="btn btn-primary topbar-logout" title="Déconnexion" aria-label="Déconnexion">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</header>
