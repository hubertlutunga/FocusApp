<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\NumberSequence;
use App\Models\Service;
use Throwable;

final class ServiceController extends Controller
{
    public function index(): void
    {
        $this->render('services.index', [
            'pageTitle' => 'Services',
            'services' => (new Service())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('services.form', [
            'pageTitle' => 'Nouveau service',
            'service' => null,
            'categories' => (new Category())->optionsByType(['service', 'mixed']),
            'formAction' => url('/services/store'),
            'submitLabel' => 'Enregistrer le service',
        ]);
    }

    public function store(): void
    {
        verify_csrf();
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['name'] === '' || $payload['category_id'] <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Nom et catégorie du service sont obligatoires.']);
            $this->redirect('/services/create');
        }

        try {
            if ($payload['code'] === '') {
                $payload['code'] = (new NumberSequence())->next('service');
            }
            (new Service())->create($payload);
            (new ActivityLog())->log('create', 'Création du service : ' . $payload['name'], 'services', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Service ajouté', 'text' => 'Le service a été créé avec succès.']);
            $this->redirect('/services');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer ce service.']);
            $this->redirect('/services/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $service = (new Service())->find($id);

        if (!$service) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Service introuvable', 'text' => 'Le service demandé n’existe pas.']);
            $this->redirect('/services');
        }

        $this->render('services.form', [
            'pageTitle' => 'Modifier un service',
            'service' => $service,
            'categories' => (new Category())->optionsByType(['service', 'mixed']),
            'formAction' => url('/services/update'),
            'submitLabel' => 'Mettre à jour le service',
        ]);
    }

    public function update(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['name'] === '' || $payload['category_id'] <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Nom et catégorie du service sont obligatoires.']);
            $this->redirect('/services/edit?id=' . $id);
        }

        try {
            if ($payload['code'] === '') {
                $payload['code'] = (new NumberSequence())->next('service');
            }
            (new Service())->updateService($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour du service #' . $id, 'services', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Service modifié', 'text' => 'Le service a été mis à jour.']);
            $this->redirect('/services');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour ce service.']);
            $this->redirect('/services/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Service introuvable', 'text' => 'Identifiant service invalide.']);
            $this->redirect('/services');
        }

        try {
            (new Service())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique du service #' . $id, 'services', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Service archivé', 'text' => 'Le service a été archivé.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver ce service.']);
        }

        $this->redirect('/services');
    }

    private function payload(): array
    {
        return [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'unit_price' => (float) ($_POST['unit_price'] ?? 0),
            'estimated_cost' => (float) ($_POST['estimated_cost'] ?? 0),
            'duration_hours' => $_POST['duration_hours'] === '' ? null : (float) $_POST['duration_hours'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
