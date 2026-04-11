<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Category;
use Throwable;

final class CategoryController extends Controller
{
    public function index(): void
    {
        $this->render('categories.index', [
            'pageTitle' => 'Catégories',
            'categories' => (new Category())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('categories.form', [
            'pageTitle' => 'Nouvelle catégorie',
            'category' => null,
            'formAction' => url('/categories/store'),
            'submitLabel' => 'Enregistrer la catégorie',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['name'] === '' || $payload['type'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le type et le nom de catégorie sont obligatoires.']);
            $this->redirect('/categories/create');
        }

        try {
            (new Category())->create($payload);
            (new ActivityLog())->log('create', 'Création de la catégorie : ' . $payload['name'], 'categories', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Catégorie ajoutée', 'text' => 'La catégorie a été créée avec succès.']);
            $this->redirect('/categories');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer cette catégorie.']);
            $this->redirect('/categories/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $category = (new Category())->find($id);

        if (!$category) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Catégorie introuvable', 'text' => 'La catégorie demandée n’existe pas.']);
            $this->redirect('/categories');
        }

        $this->render('categories.form', [
            'pageTitle' => 'Modifier une catégorie',
            'category' => $category,
            'formAction' => url('/categories/update'),
            'submitLabel' => 'Mettre à jour la catégorie',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['name'] === '' || $payload['type'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le type et le nom de catégorie sont obligatoires.']);
            $this->redirect('/categories/edit?id=' . $id);
        }

        try {
            (new Category())->updateCategory($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour de la catégorie #' . $id, 'categories', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Catégorie modifiée', 'text' => 'La catégorie a été mise à jour.']);
            $this->redirect('/categories');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour cette catégorie.']);
            $this->redirect('/categories/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Catégorie introuvable', 'text' => 'Identifiant catégorie invalide.']);
            $this->redirect('/categories');
        }

        try {
            (new Category())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique de la catégorie #' . $id, 'categories', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Catégorie archivée', 'text' => 'La catégorie a été supprimée logiquement.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver cette catégorie.']);
        }

        $this->redirect('/categories');
    }

    private function payload(): array
    {
        return [
            'type' => trim((string) ($_POST['type'] ?? 'product')),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];
    }
}
