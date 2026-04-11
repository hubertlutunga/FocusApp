<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\NumberSequence;
use App\Models\Supplier;
use Throwable;

final class SupplierController extends Controller
{
    public function index(): void
    {
        $this->render('suppliers.index', [
            'pageTitle' => 'Fournisseurs',
            'suppliers' => (new Supplier())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('suppliers.form', [
            'pageTitle' => 'Nouveau fournisseur',
            'supplier' => null,
            'formAction' => url('/suppliers/store'),
            'submitLabel' => 'Enregistrer le fournisseur',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['company_name'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom du fournisseur est obligatoire.']);
            $this->redirect('/suppliers/create');
        }

        if ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email invalide', 'text' => 'Veuillez saisir une adresse email valide.']);
            $this->redirect('/suppliers/create');
        }

        try {
            $payload['supplier_code'] = (new NumberSequence())->next('supplier');
            (new Supplier())->create($payload);
            (new ActivityLog())->log('create', 'Création d’un nouveau fournisseur : ' . $payload['company_name'], 'fournisseurs', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Fournisseur ajouté', 'text' => 'Le fournisseur a été créé avec succès.']);
            $this->redirect('/suppliers');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer ce fournisseur.']);
            $this->redirect('/suppliers/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $supplier = (new Supplier())->find($id);

        if (!$supplier) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Fournisseur introuvable', 'text' => 'Le fournisseur demandé n’existe pas.']);
            $this->redirect('/suppliers');
        }

        $this->render('suppliers.form', [
            'pageTitle' => 'Modifier un fournisseur',
            'supplier' => $supplier,
            'formAction' => url('/suppliers/update'),
            'submitLabel' => 'Mettre à jour le fournisseur',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['company_name'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom du fournisseur est obligatoire.']);
            $this->redirect('/suppliers/edit?id=' . $id);
        }

        if ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email invalide', 'text' => 'Veuillez saisir une adresse email valide.']);
            $this->redirect('/suppliers/edit?id=' . $id);
        }

        try {
            (new Supplier())->updateSupplier($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour du fournisseur #' . $id, 'fournisseurs', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Fournisseur modifié', 'text' => 'Les informations du fournisseur ont été mises à jour.']);
            $this->redirect('/suppliers');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour ce fournisseur.']);
            $this->redirect('/suppliers/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Fournisseur introuvable', 'text' => 'Identifiant fournisseur invalide.']);
            $this->redirect('/suppliers');
        }

        try {
            (new Supplier())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique du fournisseur #' . $id, 'fournisseurs', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Fournisseur archivé', 'text' => 'Le fournisseur a été supprimé logiquement.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver ce fournisseur.']);
        }

        $this->redirect('/suppliers');
    }

    private function payload(): array
    {
        return [
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'contact_name' => trim((string) ($_POST['contact_name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
