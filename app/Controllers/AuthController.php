<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->render('auth.login', [
            'pageTitle' => 'Connexion',
        ], 'auth');
    }

    public function login(): void
    {
        verify_csrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        Session::set('old_input', [
            'email' => $email,
        ]);

        if ($email === '' || $password === '') {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Champs requis',
                'text' => 'Veuillez renseigner votre email et votre mot de passe.',
            ]);
            $this->redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Email invalide',
                'text' => 'Veuillez saisir une adresse email valide.',
            ]);
            $this->redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Connexion refusée',
                'text' => 'Identifiants incorrects ou compte inactif.',
            ]);
            $this->redirect('/login');
        }

        Session::forget('old_input');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
