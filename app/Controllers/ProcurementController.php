<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\ProcurementPayment;
use App\Models\Procurement;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\NumberSequence;
use Throwable;

final class ProcurementController extends Controller
{
    public function index(): void
    {
        $this->renderForm(null);
    }

    public function create(): void
    {
        $this->renderForm(null);
    }

    public function store(): void
    {
        verify_csrf();

        $header = [
            'supplier_id' => (int) ($_POST['supplier_id'] ?? 0),
            'user_id' => (int) (Auth::id() ?? 0),
            'procurement_number' => '',
            'procurement_date' => (string) ($_POST['procurement_date'] ?? date('Y-m-d')),
            'expected_date' => ($_POST['expected_date'] ?? '') !== '' ? (string) $_POST['expected_date'] : null,
            'received_date' => null,
            'status' => (string) ($_POST['status'] ?? 'draft'),
            'payment_method' => (string) ($_POST['payment_method'] ?? 'cash'),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];

        $rawItems = old_array('items');
        $items = $this->normalizeItems($_POST['items'] ?? []);
        Session::set('old_input', [
            'supplier_id' => $header['supplier_id'],
            'procurement_date' => $header['procurement_date'],
            'expected_date' => $header['expected_date'],
            'status' => $header['status'],
            'payment_method' => $header['payment_method'],
            'notes' => $header['notes'],
            'items' => $items !== [] ? $items : $rawItems,
        ]);

        if ($header['supplier_id'] <= 0 || $items === []) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Veuillez choisir un fournisseur et au moins une ligne produit.']);
            $this->redirect('/procurements');
        }

        $subtotal = 0.0;
        foreach ($items as &$item) {
            $item['line_total'] = $item['quantity'] * $item['unit_cost'];
            $subtotal += $item['line_total'];
        }
        unset($item);

        $header['subtotal'] = $subtotal;
        $header['grand_total'] = $subtotal;
        if ($header['status'] === 'received') {
            $header['received_date'] = date('Y-m-d');
        }

        try {
            $procurementModel = new Procurement();
            $header['procurement_number'] = (new NumberSequence())->next('procurement');
            $header['payment_number'] = null;

            if ($procurementModel->supportsCreditTracking() && $header['payment_method'] !== 'credit') {
                $header['payment_number'] = (new NumberSequence())->next('procurement_payment');
            }

            $procurementId = $procurementModel->createWithItems($header, $items);
            (new ActivityLog())->log('create', 'Création de l’approvisionnement ' . $header['procurement_number'], 'approvisionnements', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Approvisionnement enregistré', 'text' => 'L’approvisionnement a été créé avec succès.']);
            $this->redirect('/procurements/show?id=' . $procurementId);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => $throwable->getMessage() ?: 'Impossible d’enregistrer cet approvisionnement.']);
            $this->redirect('/procurements');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $procurementModel = new Procurement();
        $procurement = $procurementModel->find($id);

        if (!$procurement) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Approvisionnement introuvable', 'text' => 'L’approvisionnement demandé n’existe pas.']);
            $this->redirect('/procurements');
        }

        if (in_array((string) $procurement['status'], ['received', 'cancelled'], true)) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Modification impossible', 'text' => 'Cet approvisionnement ne peut plus être modifié.']);
            $this->redirect('/procurements');
        }

        $this->renderForm($procurement);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $header = [
            'supplier_id' => (int) ($_POST['supplier_id'] ?? 0),
            'user_id' => (int) (Auth::id() ?? 0),
            'procurement_date' => (string) ($_POST['procurement_date'] ?? date('Y-m-d')),
            'expected_date' => ($_POST['expected_date'] ?? '') !== '' ? (string) $_POST['expected_date'] : null,
            'status' => (string) ($_POST['status'] ?? 'draft'),
            'payment_method' => (string) ($_POST['payment_method'] ?? 'cash'),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];

        $items = $this->normalizeItems($_POST['items'] ?? []);
        Session::set('old_input', [
            'supplier_id' => $header['supplier_id'],
            'procurement_date' => $header['procurement_date'],
            'expected_date' => $header['expected_date'],
            'status' => $header['status'],
            'payment_method' => $header['payment_method'],
            'notes' => $header['notes'],
            'items' => $items,
        ]);

        if ($id <= 0 || $header['supplier_id'] <= 0 || $items === []) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Veuillez choisir un fournisseur et au moins une ligne produit.']);
            $this->redirect('/procurements/edit?id=' . $id);
        }

        $subtotal = 0.0;
        foreach ($items as &$item) {
            $item['line_total'] = $item['quantity'] * $item['unit_cost'];
            $subtotal += $item['line_total'];
        }
        unset($item);

        $header['subtotal'] = $subtotal;
        $header['grand_total'] = $subtotal;

        try {
            $procurementModel = new Procurement();
            if ($procurementModel->supportsCreditTracking() && $header['payment_method'] !== 'credit') {
                $header['payment_number'] = (new NumberSequence())->next('procurement_payment');
            } else {
                $header['payment_number'] = null;
            }

            $procurementModel->updateWithItems($id, $header, $items);
            (new ActivityLog())->log('update', 'Mise à jour de l’approvisionnement #' . $id, 'approvisionnements', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Approvisionnement modifié', 'text' => 'L’approvisionnement a été mis à jour.']);
            $this->redirect('/procurements');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Modification impossible', 'text' => $throwable->getMessage() ?: 'Impossible de modifier cet approvisionnement.']);
            $this->redirect('/procurements/edit?id=' . $id);
        }
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $procurementModel = new Procurement();
        $procurement = $procurementModel->find($id);

        if (!$procurement) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Approvisionnement introuvable', 'text' => 'L’approvisionnement demandé n’existe pas.']);
            $this->redirect('/procurements');
        }

        $this->render('procurements.show', [
            'pageTitle' => 'Détail approvisionnement',
            'procurement' => $procurement,
            'items' => $procurementModel->items($id),
            'payments' => (new ProcurementPayment())->byProcurement($id),
        ]);
    }

    public function pay(): void
    {
        verify_csrf();
        $procurementId = (int) ($_POST['procurement_id'] ?? 0);

        if ($procurementId <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Approvisionnement introuvable', 'text' => 'Identifiant d’approvisionnement invalide.']);
            $this->redirect('/procurements');
        }

        $payload = [
            'procurement_id' => $procurementId,
            'payment_number' => (new NumberSequence())->next('procurement_payment'),
            'payment_date' => (string) ($_POST['payment_date'] ?? date('Y-m-d')),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'method' => (string) ($_POST['method'] ?? 'cash'),
            'reference' => trim((string) ($_POST['reference'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'recorded_by' => (int) (Auth::id() ?? 0),
        ];

        try {
            (new ProcurementPayment())->createForProcurement($payload);
            (new ActivityLog())->log('pay', 'Règlement de dette sur l’approvisionnement #' . $procurementId, 'approvisionnements', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Règlement enregistré', 'text' => 'Le paiement fournisseur a été enregistré.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Règlement impossible', 'text' => $throwable->getMessage() ?: 'Impossible d’enregistrer ce règlement fournisseur.']);
        }

        $this->redirect('/procurements/show?id=' . $procurementId);
    }

    public function receive(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Approvisionnement introuvable', 'text' => 'Identifiant approvisionnement invalide.']);
            $this->redirect('/procurements');
        }

        try {
            (new Procurement())->markReceived($id, (int) (Auth::id() ?? 0));
            (new ActivityLog())->log('receive', 'Réception de l’approvisionnement #' . $id, 'approvisionnements', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Approvisionnement reçu', 'text' => 'Le stock a été mis à jour.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Réception impossible', 'text' => 'Impossible de réceptionner cet approvisionnement.']);
        }

        $this->redirect('/procurements/show?id=' . $id);
    }

    public function cancel(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Approvisionnement introuvable', 'text' => 'Identifiant approvisionnement invalide.']);
            $this->redirect('/procurements');
        }

        try {
            (new Procurement())->softCancel($id);
            (new ActivityLog())->log('cancel', 'Annulation de l’approvisionnement #' . $id, 'approvisionnements', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Approvisionnement annulé', 'text' => 'L’approvisionnement a été annulé.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Annulation impossible', 'text' => 'Impossible d’annuler cet approvisionnement.']);
        }

        $this->redirect('/procurements');
    }

    public function delete(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Approvisionnement introuvable', 'text' => 'Identifiant approvisionnement invalide.']);
            $this->redirect('/procurements');
        }

        try {
            (new Procurement())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique de l’approvisionnement #' . $id, 'approvisionnements', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Approvisionnement supprimé', 'text' => 'L’approvisionnement a été supprimé.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => $throwable->getMessage() ?: 'Impossible de supprimer cet approvisionnement.']);
        }

        $this->redirect('/procurements');
    }

    private function normalizeItems(array $input): array
    {
        $productIds = $input['product_id'] ?? [];
        $quantities = $input['quantity'] ?? [];
        $unitCosts = $input['unit_cost'] ?? [];
        $items = [];

        foreach ($productIds as $index => $productId) {
            $normalizedProductId = (int) $productId;
            $quantity = (float) ($quantities[$index] ?? 0);
            $unitCost = (float) ($unitCosts[$index] ?? 0);

            if ($normalizedProductId <= 0 || $quantity <= 0 || $unitCost < 0) {
                continue;
            }

            $items[] = [
                'product_id' => $normalizedProductId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $quantity * $unitCost,
            ];
        }

        return $items;
    }

    private function renderForm(?array $procurement): void
    {
        $procurementModel = new Procurement();

        $this->render('procurements.form', [
            'pageTitle' => $procurement ? 'Modifier un approvisionnement' : 'Approvisionnements',
            'procurement' => $procurement,
            'procurementItems' => $procurement ? $procurementModel->items((int) $procurement['id']) : [],
            'procurements' => $procurementModel->all(),
            'suppliers' => (new Supplier())->options(),
            'products' => (new Product())->options(),
            'formAction' => url($procurement ? '/procurements/update' : '/procurements/store'),
        ]);
    }
}
