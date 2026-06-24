<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= e(($pageTitle ?? 'Application') . ' | ' . config('app.name')); ?></title>
	<link rel="icon" type="image/png" href="<?= e(project_asset('images/logo_focusprojet_1.png')); ?>">
	<link rel="apple-touch-icon" href="<?= e(project_asset('images/logo_focusprojet_1.png')); ?>">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?= e(asset('assets/css/app.css')); ?>">
</head>
<body class="app-shell">
	<div class="app-wrapper">
		<div class="sidebar-overlay" data-sidebar-overlay></div>
		<?php require view_path('partials.sidebar'); ?>
		<main class="app-main">
			<?php require view_path('partials.navbar'); ?>
			<div class="container-fluid py-4 app-content">
				<?= $content; ?>
			</div>
			<footer class="app-footer px-4 py-3">
				<div class="app-footer-inner">
					<span>© <?= e(date('Y')); ?> <?= e(config('app.name')); ?>. Tous droits réservés.</span>
					<span>Designed by <a href="https://hubertlutunga.com" target="_blank" rel="noopener noreferrer">Hubert Solutions</a></span>
				</div>
			</footer>
		</main>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="<?= e(asset('assets/js/app.js')); ?>"></script>
	<?php $alert = flash('alert'); ?>
	<?php if (is_array($alert)): ?>
		<script>
			Swal.fire({
				icon: <?= json_encode($alert['icon'] ?? 'info'); ?>,
				title: <?= json_encode($alert['title'] ?? 'Information'); ?>,
				text: <?= json_encode($alert['text'] ?? ''); ?>,
				confirmButtonColor: '#0d6efd'
			});
		</script>
	<?php endif; ?>
</body>
</html>
