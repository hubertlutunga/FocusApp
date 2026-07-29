<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Shop;
use Throwable;

final class ShopController extends Controller
{
    public function index(): void
    {
        $this->render('shops.index', [
            'pageTitle' => 'Boutiques / extensions',
            'shops' => (new Shop())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('shops.form', [
            'pageTitle' => 'Nouvelle boutique',
            'shop' => null,
            'formAction' => url('/shops/store'),
            'submitLabel' => 'Créer la boutique',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['code'] === '' || $payload['name'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le code et le nom de la boutique sont obligatoires.']);
            $this->redirect('/shops/create');
        }

        try {
            (new Shop())->create($payload);
            (new ActivityLog())->log('create', 'Création de la boutique ' . $payload['name'], 'boutiques', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Boutique créée', 'text' => 'La boutique a été créée avec succès.']);
            $this->redirect('/shops');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer cette boutique.']);
            $this->redirect('/shops/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $shop = (new Shop())->find($id);

        if (!$shop) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Boutique introuvable', 'text' => 'La boutique demandée n’existe pas.']);
            $this->redirect('/shops');
        }

        $this->render('shops.form', [
            'pageTitle' => 'Modifier une boutique',
            'shop' => $shop,
            'formAction' => url('/shops/update'),
            'submitLabel' => 'Mettre à jour la boutique',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['code'] === '' || $payload['name'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le code et le nom de la boutique sont obligatoires.']);
            $this->redirect('/shops/edit?id=' . $id);
        }

        try {
            (new Shop())->updateShop($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour de la boutique #' . $id, 'boutiques', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Boutique mise à jour', 'text' => 'La boutique a été modifiée.']);
            $this->redirect('/shops');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de modifier cette boutique.']);
            $this->redirect('/shops/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Boutique introuvable', 'text' => 'Identifiant boutique invalide.']);
            $this->redirect('/shops');
        }

        try {
            (new Shop())->softDelete($id);
            (new ActivityLog())->log('delete', 'Archivage de la boutique #' . $id, 'boutiques', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Boutique archivée', 'text' => 'La boutique a été archivée.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Archivage impossible', 'text' => 'Impossible d’archiver cette boutique.']);
        }

        $this->redirect('/shops');
    }

    private function payload(): array
    {
        return [
            'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'manager_name' => trim((string) ($_POST['manager_name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}