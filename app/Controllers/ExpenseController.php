<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use App\Models\NumberSequence;
use App\Models\Supplier;
use Throwable;

final class ExpenseController extends Controller
{
    public function index(): void
    {
        $this->render('expenses.index', [
            'pageTitle' => 'Dépenses',
            'expenses' => (new Expense())->all(),
        ]);
    }

    public function create(): void
    {
        $expenseModel = new Expense();

        $this->render('expenses.form', [
            'pageTitle' => 'Nouvelle dépense',
            'expense' => null,
            'categories' => (new ExpenseCategory())->options(),
            'suppliers' => (new Supplier())->options(),
            'supportsCreditTracking' => $expenseModel->supportsCreditTracking(),
            'formAction' => url('/expenses/store'),
            'submitLabel' => 'Enregistrer la dépense',
        ]);
    }

    public function store(): void
    {
        verify_csrf();
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['expense_category_id'] <= 0 || $payload['description'] === '' || $payload['amount'] <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Catégorie, description et montant sont obligatoires.']);
            $this->redirect('/expenses/create');
        }

        if ($payload['payment_method'] === 'credit' && !$payload['supplier_id']) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Tiers requis', 'text' => 'Veuillez sélectionner le tiers ou fournisseur pour une dépense à crédit.']);
            $this->redirect('/expenses/create');
        }

        if ($payload['payment_method'] === 'credit' && !(new Expense())->supportsCreditTracking()) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Migration requise', 'text' => 'Le mode à crédit nécessite la migration de la base de données des dépenses.']);
            $this->redirect('/expenses/create');
        }

        try {
            $payload['expense_number'] = (new NumberSequence())->next('expense');
            $expenseId = (new Expense())->create($payload);
            (new ActivityLog())->log('create', 'Création de la dépense ' . $payload['expense_number'], 'depenses', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Dépense enregistrée', 'text' => 'La dépense a été enregistrée avec succès.']);
            $this->redirect('/expenses/show?id=' . $expenseId);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => $throwable->getMessage() ?: 'Impossible d’enregistrer cette dépense.']);
            $this->redirect('/expenses/create');
        }
    }

    public function storeSupplier(): void
    {
        verify_csrf();

        $payload = [
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'contact_name' => trim((string) ($_POST['contact_name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'is_active' => 1,
        ];

        if ($payload['company_name'] === '') {
            $this->respondJson([
                'success' => false,
                'message' => 'Le nom du fournisseur est obligatoire.',
            ], 422);
        }

        if ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $this->respondJson([
                'success' => false,
                'message' => 'Veuillez saisir une adresse email valide.',
            ], 422);
        }

        try {
            $payload['supplier_code'] = (new NumberSequence())->next('supplier');
            $supplierId = (new Supplier())->create($payload);
            (new ActivityLog())->log('create', 'Création d’un nouveau fournisseur : ' . $payload['company_name'], 'fournisseurs', Auth::id());

            $this->respondJson([
                'success' => true,
                'message' => 'Le fournisseur a été créé avec succès.',
                'supplier' => [
                    'id' => $supplierId,
                    'company_name' => $payload['company_name'],
                    'supplier_code' => $payload['supplier_code'],
                ],
            ]);
        } catch (Throwable $throwable) {
            $this->respondJson([
                'success' => false,
                'message' => $throwable->getMessage() ?: 'Impossible de créer ce fournisseur.',
            ], 422);
        }
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $expenseModel = new Expense();
        $expense = $expenseModel->find($id);

        if (!$expense) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Dépense introuvable', 'text' => 'La dépense demandée n’existe pas.']);
            $this->redirect('/expenses');
        }

        $this->render('expenses.show', [
            'pageTitle' => 'Détail dépense',
            'expense' => $expense,
            'supportsCreditTracking' => $expenseModel->supportsCreditTracking(),
            'payments' => (new ExpensePayment())->byExpense($id),
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $expenseModel = new Expense();
        $expense = $expenseModel->find($id);

        if (!$expense) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Dépense introuvable', 'text' => 'La dépense demandée n’existe pas.']);
            $this->redirect('/expenses');
        }

        $this->render('expenses.form', [
            'pageTitle' => 'Modifier une dépense',
            'expense' => $expense,
            'categories' => (new ExpenseCategory())->options(),
            'suppliers' => (new Supplier())->options(),
            'supportsCreditTracking' => $expenseModel->supportsCreditTracking(),
            'formAction' => url('/expenses/update'),
            'submitLabel' => 'Mettre à jour la dépense',
        ]);
    }

    public function update(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['expense_category_id'] <= 0 || $payload['description'] === '' || $payload['amount'] <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Catégorie, description et montant sont obligatoires.']);
            $this->redirect('/expenses/edit?id=' . $id);
        }

        try {
            (new Expense())->updateExpense($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour de la dépense #' . $id, 'depenses', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Dépense mise à jour', 'text' => 'La dépense a été modifiée avec succès.']);
            $this->redirect('/expenses/show?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => $throwable->getMessage() ?: 'Impossible de mettre à jour cette dépense.']);
            $this->redirect('/expenses/edit?id=' . $id);
        }
    }

    public function pay(): void
    {
        verify_csrf();

        $expenseId = (int) ($_POST['expense_id'] ?? 0);
        if ($expenseId <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Dette introuvable', 'text' => 'Identifiant de dépense invalide.']);
            $this->redirect('/expenses');
        }

        if (!(new Expense())->supportsCreditTracking()) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Migration requise', 'text' => 'Les règlements de dépenses nécessitent la migration de la base de données.']);
            $this->redirect('/expenses/show?id=' . $expenseId);
        }

        $payload = [
            'expense_id' => $expenseId,
            'payment_date' => (string) ($_POST['payment_date'] ?? date('Y-m-d')),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'method' => (string) ($_POST['method'] ?? 'cash'),
            'reference' => trim((string) ($_POST['reference'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'recorded_by' => (int) (Auth::id() ?? 0),
        ];

        try {
            (new ExpensePayment())->createForExpense($payload);
            (new ActivityLog())->log('pay', 'Règlement de la dette sur la dépense #' . $expenseId, 'depenses', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Règlement enregistré', 'text' => 'Le paiement de cette dette a été enregistré.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Règlement impossible', 'text' => $throwable->getMessage() ?: 'Impossible d’enregistrer ce règlement.']);
        }

        $this->redirect('/expenses/show?id=' . $expenseId);
    }

    public function delete(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        try {
            (new Expense())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique de la dépense #' . $id, 'depenses', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Dépense archivée', 'text' => 'La dépense a été archivée.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver cette dépense.']);
        }

        $this->redirect('/expenses');
    }

    private function payload(): array
    {
        return [
            'expense_category_id' => (int) ($_POST['expense_category_id'] ?? 0),
            'supplier_id' => ($_POST['supplier_id'] ?? '') !== '' ? (int) $_POST['supplier_id'] : null,
            'expense_date' => (string) ($_POST['expense_date'] ?? date('Y-m-d')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'payment_method' => (string) ($_POST['payment_method'] ?? 'cash'),
            'created_by' => (int) (Auth::id() ?? 0),
        ];
    }

    private function respondJson(array $payload, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
