<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class AdminMiddleware
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

        if (!Auth::hasRole('administrateur')) {
            Session::flash('alert', [
                'icon' => 'warning',
                'title' => 'Accès refusé',
                'text' => 'Cette rubrique est réservée à l’administrateur.',
            ]);
            redirect('/dashboard');
        }
    }
}