<div class="container py-5">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-lg-6 text-center">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="display-3 fw-bold text-primary mb-3">404</div>
                    <h1 class="h3 mb-3"><?= e($title ?? 'Page introuvable'); ?></h1>
                    <p class="text-muted mb-4"><?= e($message ?? 'La page demandée est introuvable.'); ?></p>
                    <a href="<?= e(url('/login')); ?>" class="btn btn-primary">Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>
