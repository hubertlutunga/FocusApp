<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            Session::flash('alert', [
                'icon' => 'warning',
                'title' => 'Authentification requise',
                'text' => 'Veuillez vous connecter pour accéder à cette page.',
            ]);
            redirect('/login');
        }
    }
}
