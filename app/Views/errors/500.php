<div class="container py-5">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-lg-7 text-center">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="display-3 fw-bold text-danger mb-3">500</div>
                    <h1 class="h3 mb-3"><?= e($title ?? 'Erreur interne'); ?></h1>
                    <p class="text-muted mb-4"><?= e($message ?? 'Une erreur inattendue est survenue.'); ?></p>
                    <a href="<?= e(url('/dashboard')); ?>" class="btn btn-primary">Retour au tableau de bord</a>
                </div>
            </div>
        </div>
    </div>
</div>
