<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    public function render(string $view, array $data = [], string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = view_path($view);
        $layoutFile = view_path('layouts.' . $layout);

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vue introuvable : ' . e($view);
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
        Session::forget('old_input');
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }
}
