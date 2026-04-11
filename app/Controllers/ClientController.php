<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\NumberSequence;
use Throwable;

final class ClientController extends Controller
{
    public function index(): void
    {
        $this->render('clients.index', [
            'pageTitle' => 'Clients',
            'clients' => (new Client())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('clients.form', [
            'pageTitle' => 'Nouveau client',
            'client' => null,
            'formAction' => url('/clients/store'),
            'submitLabel' => 'Enregistrer le client',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['company_name'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom du client est obligatoire.']);
            $this->redirect('/clients/create');
        }

        if ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email invalide', 'text' => 'Veuillez saisir une adresse email valide.']);
            $this->redirect('/clients/create');
        }

        try {
            $payload['client_code'] = (new NumberSequence())->next('client');
            (new Client())->create($payload);
            (new ActivityLog())->log('create', 'Création d’un nouveau client : ' . $payload['company_name'], 'clients', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Client ajouté', 'text' => 'Le client a été créé avec succès.']);
            $this->redirect('/clients');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer ce client.']);
            $this->redirect('/clients/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $client = (new Client())->find($id);

        if (!$client) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Client introuvable', 'text' => 'Le client demandé n’existe pas.']);
            $this->redirect('/clients');
        }

        $this->render('clients.form', [
            'pageTitle' => 'Modifier un client',
            'client' => $client,
            'formAction' => url('/clients/update'),
            'submitLabel' => 'Mettre à jour le client',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['company_name'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Le nom du client est obligatoire.']);
            $this->redirect('/clients/edit?id=' . $id);
        }

        if ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Email invalide', 'text' => 'Veuillez saisir une adresse email valide.']);
            $this->redirect('/clients/edit?id=' . $id);
        }

        try {
            (new Client())->updateClient($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour du client #' . $id, 'clients', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Client modifié', 'text' => 'Les informations du client ont été mises à jour.']);
            $this->redirect('/clients');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour ce client.']);
            $this->redirect('/clients/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Client introuvable', 'text' => 'Identifiant client invalide.']);
            $this->redirect('/clients');
        }

        try {
            (new Client())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique du client #' . $id, 'clients', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Client archivé', 'text' => 'Le client a été supprimé logiquement.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver ce client.']);
        }

        $this->redirect('/clients');
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
            'tax_number' => trim((string) ($_POST['tax_number'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
