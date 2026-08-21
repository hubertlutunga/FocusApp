<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\StarlinkSubscription;
use Throwable;

final class StarlinkSubscriptionController extends Controller
{
    public function index(): void
    {
        $this->render('starlink_subscriptions.index', [
            'pageTitle' => 'Abonnements Starlink',
            'subscriptions' => (new StarlinkSubscription())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('starlink_subscriptions.form', [
            'pageTitle' => 'Nouvel abonnement Starlink',
            'subscription' => null,
            'clients' => (new Client())->options(),
            'formAction' => url('/starlink-subscriptions/store'),
            'submitLabel' => 'Enregistrer l\'abonnement',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if (!$this->isPayloadValid($payload)) {
            $this->redirect('/starlink-subscriptions/create');
        }

        try {
            $payload['created_by'] = (int) (Auth::id() ?? 0);
            (new StarlinkSubscription())->create($payload);
            (new ActivityLog())->log('create', 'Création d\'un abonnement Starlink', 'starlink_subscriptions', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Abonnement ajouté', 'text' => 'L\'abonnement Starlink a été enregistré.']);
            $this->redirect('/starlink-subscriptions');
        } catch (Throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer cet abonnement.']);
            $this->redirect('/starlink-subscriptions/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $subscription = (new StarlinkSubscription())->find($id);

        if (!$subscription) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Abonnement introuvable', 'text' => 'L\'abonnement demandé n\'existe pas.']);
            $this->redirect('/starlink-subscriptions');
        }

        $this->render('starlink_subscriptions.form', [
            'pageTitle' => 'Modifier abonnement Starlink',
            'subscription' => $subscription,
            'clients' => (new Client())->options(),
            'formAction' => url('/starlink-subscriptions/update'),
            'submitLabel' => 'Mettre à jour',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || !$this->isPayloadValid($payload)) {
            $this->redirect('/starlink-subscriptions/edit?id=' . $id);
        }

        try {
            (new StarlinkSubscription())->updateSubscription($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour abonnement Starlink #' . $id, 'starlink_subscriptions', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Abonnement modifié', 'text' => 'Les données de l\'abonnement ont été mises à jour.']);
            $this->redirect('/starlink-subscriptions');
        } catch (Throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de modifier cet abonnement.']);
            $this->redirect('/starlink-subscriptions/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Abonnement introuvable', 'text' => 'Identifiant invalide.']);
            $this->redirect('/starlink-subscriptions');
        }

        try {
            (new StarlinkSubscription())->softDelete($id);
            (new ActivityLog())->log('delete', 'Archivage abonnement Starlink #' . $id, 'starlink_subscriptions', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Abonnement archivé', 'text' => 'L\'abonnement a été archivé.']);
        } catch (Throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d\'archiver cet abonnement.']);
        }

        $this->redirect('/starlink-subscriptions');
    }

    private function payload(): array
    {
        return [
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'line_label' => trim((string) ($_POST['line_label'] ?? '')),
            'subscription_number' => trim((string) ($_POST['subscription_number'] ?? '')),
            'plan_name' => trim((string) ($_POST['plan_name'] ?? '')),
            'start_date' => (string) ($_POST['start_date'] ?? ''),
            'end_date' => (string) ($_POST['end_date'] ?? ''),
            'monthly_amount' => (float) ($_POST['monthly_amount'] ?? 0),
            'reminder_days' => max(0, (int) ($_POST['reminder_days'] ?? 7)),
            'status' => in_array((string) ($_POST['status'] ?? 'active'), ['active', 'expired', 'cancelled'], true)
                ? (string) $_POST['status']
                : 'active',
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];
    }

    private function isPayloadValid(array $payload): bool
    {
        if ($payload['client_id'] <= 0 || $payload['line_label'] === '' || $payload['end_date'] === '') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Client, libellé de ligne et date échéance sont obligatoires.']);
            return false;
        }

        if ($payload['start_date'] !== '' && $payload['end_date'] < $payload['start_date']) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Dates invalides', 'text' => 'La date d\'échéance doit être postérieure à la date de début.']);
            return false;
        }

        return true;
    }
}
