<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
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