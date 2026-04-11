<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Unit;
use Throwable;

final class UnitController extends Controller
{
    public function index(): void
    {
        $this->render('units.index', [
            'pageTitle' => 'Unités',
            'units' => (new Unit())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('units.form', [
            'pageTitle' => 'Nouvelle unité',
            'unit' => null,
            'formAction' => url('/units/store'),
            'submitLabel' => 'Enregistrer l’unité',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['name'] === '' || $payload['symbol'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom et le symbole sont obligatoires.']);
            $this->redirect('/units/create');
        }

        try {
            (new Unit())->create($payload);
            (new ActivityLog())->log('create', 'Création de l’unité : ' . $payload['name'], 'unites', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Unité ajoutée', 'text' => 'L’unité a été créée avec succès.']);
            $this->redirect('/units');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer cette unité.']);
            $this->redirect('/units/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $unit = (new Unit())->find($id);

        if (!$unit) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Unité introuvable', 'text' => 'L’unité demandée n’existe pas.']);
            $this->redirect('/units');
        }

        $this->render('units.form', [
            'pageTitle' => 'Modifier une unité',
            'unit' => $unit,
            'formAction' => url('/units/update'),
            'submitLabel' => 'Mettre à jour l’unité',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['name'] === '' || $payload['symbol'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom et le symbole sont obligatoires.']);
            $this->redirect('/units/edit?id=' . $id);
        }

        try {
            (new Unit())->updateUnit($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour de l’unité #' . $id, 'unites', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Unité modifiée', 'text' => 'L’unité a été mise à jour.']);
            $this->redirect('/units');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour cette unité.']);
            $this->redirect('/units/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Unité introuvable', 'text' => 'Identifiant unité invalide.']);
            $this->redirect('/units');
        }

        try {
            (new Unit())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique de l’unité #' . $id, 'unites', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Unité archivée', 'text' => 'L’unité a été supprimée logiquement.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver cette unité.']);
        }

        $this->redirect('/units');
    }

    private function payload(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'symbol' => trim((string) ($_POST['symbol'] ?? '')),
        ];
    }
}
