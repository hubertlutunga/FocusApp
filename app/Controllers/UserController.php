<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\User;
use Throwable;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->render('users.index', [
            'pageTitle' => 'Utilisateurs',
            'users' => (new User())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('users.form', [
            'pageTitle' => 'Nouvel utilisateur',
            'userData' => null,
            'roles' => (new User())->roleOptions(),
            'formAction' => url('/users/store'),
            'submitLabel' => 'Créer le compte',
            'passwordRequired' => true,
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if (!$this->isPayloadValid($payload, true)) {
            $this->redirect('/users/create');
        }

        $userModel = new User();
        if ($userModel->emailExists($payload['email'])) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email déjà utilisé', 'text' => 'Veuillez choisir une autre adresse email.']);
            $this->redirect('/users/create');
        }

        try {
            $userModel->createUser([
                'role_id' => $payload['role_id'],
                'full_name' => $payload['full_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'password' => password_hash($payload['password'], PASSWORD_BCRYPT),
                'is_active' => $payload['is_active'],
            ]);

            (new ActivityLog())->log('create', 'Création d’un utilisateur : ' . $payload['full_name'], 'utilisateurs', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Utilisateur créé', 'text' => 'Le compte utilisateur a été créé avec succès.']);
            $this->redirect('/users');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer cet utilisateur.']);
            $this->redirect('/users/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = (new User())->findById($id);

        if (!$user) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Utilisateur introuvable', 'text' => 'Le compte demandé n’existe pas.']);
            $this->redirect('/users');
        }

        $this->render('users.form', [
            'pageTitle' => 'Modifier un utilisateur',
            'userData' => $user,
            'roles' => (new User())->roleOptions(),
            'formAction' => url('/users/update'),
            'submitLabel' => 'Mettre à jour le compte',
            'passwordRequired' => false,
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || !$this->isPayloadValid($payload, false)) {
            $this->redirect('/users/edit?id=' . $id);
        }

        $userModel = new User();
        if ($userModel->emailExists($payload['email'], $id)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email déjà utilisé', 'text' => 'Veuillez choisir une autre adresse email.']);
            $this->redirect('/users/edit?id=' . $id);
        }

        try {
            $updateData = [
                'role_id' => $payload['role_id'],
                'full_name' => $payload['full_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'is_active' => $payload['is_active'],
                'password' => $payload['password'] !== '' ? password_hash($payload['password'], PASSWORD_BCRYPT) : '',
            ];

            $userModel->updateUser($id, $updateData);
            (new ActivityLog())->log('update', 'Mise à jour de l’utilisateur #' . $id, 'utilisateurs', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Utilisateur mis à jour', 'text' => 'Le compte utilisateur a été modifié.']);
            $this->redirect('/users');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de modifier cet utilisateur.']);
            $this->redirect('/users/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Utilisateur introuvable', 'text' => 'Identifiant utilisateur invalide.']);
            $this->redirect('/users');
        }

        if ((int) (Auth::id() ?? 0) === $id) {
            Session::flash('alert', ['icon' => 'warning', 'title' => 'Action non autorisée', 'text' => 'Vous ne pouvez pas archiver votre propre compte.']);
            $this->redirect('/users');
        }

        try {
            (new User())->softDelete($id);
            (new ActivityLog())->log('delete', 'Archivage de l’utilisateur #' . $id, 'utilisateurs', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Utilisateur archivé', 'text' => 'Le compte utilisateur a été archivé.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Archivage impossible', 'text' => 'Impossible d’archiver cet utilisateur.']);
        }

        $this->redirect('/users');
    }

    private function payload(): array
    {
        return [
            'role_id' => (int) ($_POST['role_id'] ?? 0),
            'full_name' => trim((string) ($_POST['full_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function isPayloadValid(array $payload, bool $passwordRequired): bool
    {
        if ($payload['role_id'] <= 0 || $payload['full_name'] === '' || $payload['email'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom complet, le rôle et l’email sont obligatoires.']);
            return false;
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email invalide', 'text' => 'Veuillez saisir une adresse email valide.']);
            return false;
        }

        if ($passwordRequired && $payload['password'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mot de passe requis', 'text' => 'Veuillez saisir un mot de passe.']);
            return false;
        }

        if ($payload['password'] !== '' && strlen($payload['password']) < 8) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mot de passe trop court', 'text' => 'Le mot de passe doit contenir au moins 8 caractères.']);
            return false;
        }

        if ($payload['password'] !== $payload['password_confirmation']) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Confirmation invalide', 'text' => 'Les mots de passe ne correspondent pas.']);
            return false;
        }

        return true;
    }
}