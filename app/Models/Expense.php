<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;
use Throwable;

final class Expense extends Model
{
    public function all(): array
    {
        $sql = 'SELECT e.*, ec.name AS category_name, s.company_name AS supplier_name, u.full_name AS user_name
                FROM expenses e
                INNER JOIN expense_categories ec ON ec.id = e.expense_category_id
                LEFT JOIN suppliers s ON s.id = e.supplier_id
                INNER JOIN users u ON u.id = e.created_by
                WHERE e.deleted_at IS NULL
                ORDER BY e.id DESC';

        $rows = $this->db->query($sql)->fetchAll();

        return array_map(fn (array $row): array => $this->normalizeExpenseRow($row), $rows);
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT e.*, ec.name AS category_name, s.company_name AS supplier_name, u.full_name AS user_name
                FROM expenses e
                INNER JOIN expense_categories ec ON ec.id = e.expense_category_id
                LEFT JOIN suppliers s ON s.id = e.supplier_id
                INNER JOIN users u ON u.id = e.created_by
                WHERE e.id = :id AND e.deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $expense = $statement->fetch();

        return $expense ? $this->normalizeExpenseRow($expense) : null;
    }

    public function create(array $data): int
    {
        $supportsCreditTracking = $this->supportsCreditTracking();

        if (!$supportsCreditTracking && $data['payment_method'] === 'credit') {
            throw new RuntimeException('La base de donnees doit etre migree avant d enregistrer une depense.');
        }

        $paymentNumber = null;
        if ($supportsCreditTracking && $data['payment_method'] !== 'credit') {
            $paymentNumber = $data['payment_number'] ?? (new NumberSequence())->next('expense_payment');
        }

        $this->db->beginTransaction();

        try {
            $sql = $supportsCreditTracking
                ? 'INSERT INTO expenses (expense_category_id, supplier_id, expense_number, expense_date, description, amount, payment_method, payment_status, amount_paid, balance_due, settled_at, created_by, deleted_at, created_at, updated_at)
                    VALUES (:expense_category_id, :supplier_id, :expense_number, :expense_date, :description, :amount, :payment_method, :payment_status, :amount_paid, :balance_due, :settled_at, :created_by, NULL, NOW(), NOW())'
                : 'INSERT INTO expenses (expense_category_id, supplier_id, expense_number, expense_date, description, amount, payment_method, created_by, deleted_at, created_at, updated_at)
                    VALUES (:expense_category_id, :supplier_id, :expense_number, :expense_date, :description, :amount, :payment_method, :created_by, NULL, NOW(), NOW())';
            $statement = $this->db->prepare($sql);
            $payload = [
                'expense_category_id' => $data['expense_category_id'],
                'supplier_id' => $data['supplier_id'],
                'expense_number' => $data['expense_number'],
                'expense_date' => $data['expense_date'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'created_by' => $data['created_by'],
            ];

            if ($supportsCreditTracking) {
                $payload['payment_status'] = $data['payment_method'] === 'credit' ? 'unpaid' : 'paid';
                $payload['amount_paid'] = $data['payment_method'] === 'credit' ? 0 : $data['amount'];
                $payload['balance_due'] = $data['payment_method'] === 'credit' ? $data['amount'] : 0;
                $payload['settled_at'] = $data['payment_method'] === 'credit' ? null : date('Y-m-d H:i:s');
            }

            $statement->execute($payload);

            $expenseId = (int) $this->db->lastInsertId();

            if ($supportsCreditTracking && $data['payment_method'] !== 'credit') {
                $paymentStatement = $this->db->prepare('INSERT INTO expense_payments (expense_id, payment_number, payment_date, amount, method, reference, notes, recorded_by, deleted_at, created_at, updated_at)
                    VALUES (:expense_id, :payment_number, :payment_date, :amount, :method, :reference, :notes, :recorded_by, NULL, NOW(), NOW())');
                $paymentStatement->execute([
                    'expense_id' => $expenseId,
                    'payment_number' => $paymentNumber,
                    'payment_date' => $data['expense_date'],
                    'amount' => $data['amount'],
                    'method' => $data['payment_method'],
                    'reference' => null,
                    'notes' => 'Règlement initial à la création de la dépense.',
                    'recorded_by' => $data['created_by'],
                ]);
            }

            $this->refreshPaymentStatus($expenseId);
            $this->db->commit();

            return $expenseId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    public function updateExpense(int $id, array $data): void
    {
        if ($this->supportsCreditTracking()) {
            $paidStatement = $this->db->prepare('SELECT COALESCE(SUM(amount), 0) FROM expense_payments WHERE expense_id = :expense_id AND deleted_at IS NULL');
            $paidStatement->execute(['expense_id' => $id]);
            $alreadyPaid = (float) $paidStatement->fetchColumn();

            if ($alreadyPaid > (float) $data['amount']) {
                throw new RuntimeException('Le montant de la dépense ne peut pas être inférieur aux règlements déjà enregistrés.');
            }
        }

        $payload = [
            'id' => $id,
            'expense_category_id' => $data['expense_category_id'],
            'supplier_id' => $data['supplier_id'],
            'expense_date' => $data['expense_date'],
            'description' => $data['description'],
            'amount' => $data['amount'],
        ];

        $sql = 'UPDATE expenses SET expense_category_id = :expense_category_id, supplier_id = :supplier_id, expense_date = :expense_date, description = :description, amount = :amount, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
        $statement = $this->db->prepare($sql);
        $statement->execute($payload);

        $this->refreshPaymentStatus($id);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE expenses SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function refreshPaymentStatus(int $id): void
    {
        if (!$this->supportsCreditTracking()) {
            return;
        }

        $statement = $this->db->prepare('SELECT e.amount, COALESCE(SUM(ep.amount), 0) AS paid
            FROM expenses e
            LEFT JOIN expense_payments ep ON ep.expense_id = e.id AND ep.deleted_at IS NULL
            WHERE e.id = :id AND e.deleted_at IS NULL
            GROUP BY e.id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if (!$row) {
            return;
        }

        $amount = (float) $row['amount'];
        $paid = (float) $row['paid'];
        $balance = max($amount - $paid, 0);

        $status = 'unpaid';
        $settledAt = null;
        if ($paid >= $amount && $amount > 0) {
            $status = 'paid';
            $settledAt = date('Y-m-d H:i:s');
        } elseif ($paid > 0) {
            $status = 'partial_paid';
        }

        $update = $this->db->prepare('UPDATE expenses SET amount_paid = :amount_paid, balance_due = :balance_due, payment_status = :payment_status, settled_at = :settled_at, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'amount_paid' => $paid,
            'balance_due' => $balance,
            'payment_status' => $status,
            'settled_at' => $settledAt,
            'id' => $id,
        ]);
    }

    public function supportsCreditTracking(): bool
    {
        $columnStatement = $this->db->query("SHOW COLUMNS FROM expenses LIKE 'payment_status'");
        $paymentsTableStatement = $this->db->query("SHOW TABLES LIKE 'expense_payments'");

        return (bool) $columnStatement->fetch() && (bool) $paymentsTableStatement->fetch();
    }

    private function normalizeExpenseRow(array $row): array
    {
        if ($this->supportsCreditTracking()) {
            return $row;
        }

        $row['payment_status'] = 'paid';
        $row['amount_paid'] = $row['amount'];
        $row['balance_due'] = 0;
        $row['settled_at'] = $row['settled_at'] ?? $row['created_at'] ?? null;

        return $row;
    }
}
