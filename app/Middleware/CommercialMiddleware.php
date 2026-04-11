<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class CommercialMiddleware
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

        if (!Auth::hasRole(['administrateur', 'caisse', 'caissier', 'gestionnaire_stock'])) {
            Session::flash('alert', [
                'icon' => 'warning',
                'title' => 'Accès refusé',
                'text' => 'Cette rubrique est réservée aux profils commercial, caisse et gestionnaire de stock.',
            ]);
            redirect('/dashboard');
        }
    }
}