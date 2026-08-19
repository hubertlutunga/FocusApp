<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'Impression') . ' | ' . config('app.name')); ?></title>
</head>
<body>
    <?= $content; ?>
</body>
</html>
