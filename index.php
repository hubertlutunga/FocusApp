<?php

declare(strict_types=1);

use App\Core\Router;

$appConfig = require __DIR__ . '/config/app.php';

if (($appConfig['base_url'] ?? '') === '') {
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = str_replace('\\', '/', dirname($scriptName));
    $appConfig['base_url'] = $basePath === '/' || $basePath === '.' ? '' : rtrim($basePath, '/');
}

$GLOBALS['config'] = [
    'app' => $appConfig,
    'database' => file_exists(__DIR__ . '/config/database.local.php')
        ? require __DIR__ . '/config/database.local.php'
        : require __DIR__ . '/config/database.php',
];

require __DIR__ . '/app/Helpers/functions.php';

date_default_timezone_set((string) config('app.timezone', 'Africa/Kinshasa'));

session_name((string) config('app.session_name', 'FOCUS_GROUP_SESSID'));
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$router = new Router();
require __DIR__ . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
