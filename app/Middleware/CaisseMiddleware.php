<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class CaisseMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            redirect('/login');
        }

        if (!Auth::hasRole(['administrateur', 'caisse', 'caissier'])) {
            Session::flash('alert', [
                'icon' => 'warning',
                'title' => 'Accès refusé',
                'text' => 'Cette rubrique est réservée au profil caisse.',
            ]);
            redirect('/dashboard');
        }
    }
}