<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;

final class ActivityLogController extends Controller
{
    public function index(): void
    {
        $module = trim((string) ($_GET['module'] ?? ''));
        $action = trim((string) ($_GET['action'] ?? ''));
        $activityLog = new ActivityLog();

        $this->render('activitylogs.index', [
            'pageTitle' => 'Journal d’activité',
            'logs' => $activityLog->all($module !== '' ? $module : null, $action !== '' ? $action : null),
            'modules' => $activityLog->modules(),
            'actions' => $activityLog->actions(),
            'selectedModule' => $module,
            'selectedAction' => $action,
        ]);
    }
}
