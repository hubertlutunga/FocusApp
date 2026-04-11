<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class CommercialMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
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