<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
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
        $this->render('expenses.form', [
            'pageTitle' => 'Nouvelle dépense',
            'expense' => null,
            'categories' => (new ExpenseCategory())->options(),
            'suppliers' => (new Supplier())->options(),
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

        try {
            $payload['expense_number'] = (new NumberSequence())->next('expense');
            (new Expense())->create($payload);
            (new ActivityLog())->log('create', 'Création de la dépense ' . $payload['expense_number'], 'depenses', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Dépense enregistrée', 'text' => 'La dépense a été enregistrée avec succès.']);
            $this->redirect('/expenses');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible d’enregistrer cette dépense.']);
            $this->redirect('/expenses/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $expense = (new Expense())->find($id);

        if (!$expense) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Dépense introuvable', 'text' => 'La dépense demandée n’existe pas.']);
            $this->redirect('/expenses');
        }

        $this->render('expenses.form', [
            'pageTitle' => 'Modifier une dépense',
            'expense' => $expense,
            'categories' => (new ExpenseCategory())->options(),
            'suppliers' => (new Supplier())->options(),
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
            $this->redirect('/expenses');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour cette dépense.']);
            $this->redirect('/expenses/edit?id=' . $id);
        }
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
}
