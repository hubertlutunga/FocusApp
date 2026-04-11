<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class StockManagerMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            redirect('/login');
        }

        if (!Auth::hasRole(['administrateur', 'gestionnaire_stock'])) {
            Session::flash('alert', [
                'icon' => 'warning',
                'title' => 'Accès refusé',
                'text' => 'Cette rubrique est réservée au gestionnaire de stock.',
            ]);
            redirect('/dashboard');
        }
    }
}