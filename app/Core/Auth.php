<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\ActivityLog;
use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        $user = Session::get('auth.user');
        return is_array($user) ? $user : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $userId = self::user()['id'] ?? null;

        if ($userId === null || $userId === '') {
            return null;
        }

        return (int) $userId;
    }

    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findActiveByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        Session::regenerate();
        $userModel->updateLastLogin((int) $user['id']);
        unset($user['password']);
        Session::set('auth.user', $user);

        (new ActivityLog())->log(
            'login',
            'Connexion utilisateur réussie',
            'authentification',
            (int) $user['id']
        );

        return true;
    }

    public static function logout(): void
    {
        $userId = self::id();

        if ($userId) {
            (new ActivityLog())->log('logout', 'Déconnexion utilisateur', 'authentification', $userId);
        }

        Session::forget('auth.user');
        Session::regenerate();
    }

    public static function hasRole(array|string $roles): bool
    {
        $roles = (array) $roles;
        $currentRole = self::user()['role_code'] ?? null;
        return $currentRole !== null && in_array($currentRole, $roles, true);
    }
}
