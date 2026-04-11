<?php
$alert = flash('alert');
$alertClass = match ($alert['icon'] ?? 'info') {
    'success' => 'success',
    'error' => 'danger',
    'warning' => 'warning',
    default => 'info',
};
?>

<div class="container py-5 auth-shell">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-4 col-lg-5 col-md-7">
            <div class="card border-0 auth-card auth-card-clean">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center auth-header mb-4">
                        <div class="auth-logo-wrap mb-4">
                            <img src="<?= e(project_asset('images/logo_focusprojet_bleu.png')); ?>" alt="Focus Group" class="auth-logo">
                        </div>
                        <h2 class="fw-bold mb-2 auth-title">Connexion sécurisée</h2>
                        <p class="auth-subtitle mb-0">Accédez à votre espace de gestion en toute simplicité.</p>
                    </div>

                    <?php if (is_array($alert)): ?>
                        <div class="alert alert-<?= e($alertClass); ?>" role="alert">
                            <div class="fw-semibold mb-1"><?= e($alert['title'] ?? 'Information'); ?></div>
                            <div><?= e($alert['text'] ?? ''); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= e(url('/login')); ?>" class="row g-3 auth-form">
                        <?= csrf_field(); ?>
                        <div class="col-12">
                            <label for="email" class="form-label auth-label">Adresse email</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg auth-input" value="<?= e(old('email')); ?>" placeholder="nom@entreprise.com" required>
                        </div>

                        <div class="col-12">
                            <label for="password" class="form-label auth-label">Mot de passe</label>
                            <input type="password" id="password" name="password" class="form-control form-control-lg auth-input" placeholder="••••••••" required>
                        </div>

                        <div class="col-12 d-grid mt-2">
                            <button type="submit" class="btn btn-primary btn-lg auth-submit-btn">Se connecter</button>
                        </div>
                    </form>

                    <div class="auth-note mt-4">
                        <i class="bi bi-shield-lock"></i>
                        <span>Accès réservé aux utilisateurs autorisés.</span>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted small mt-4 auth-footer-note">
                &copy; <?= e(date('Y')); ?> Hubert Solutions. Tous droits réservés.
            </div>
        </div>
    </div>
</div>
