<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;
use Throwable;

final class ExpensePayment extends Model
{
    public function byExpense(int $expenseId): array
    {
        if (!$this->supportsCreditTracking()) {
            return [];
        }

        $sql = 'SELECT ep.*, u.full_name AS user_name
                FROM expense_payments ep
                INNER JOIN users u ON u.id = ep.recorded_by
                WHERE ep.expense_id = :expense_id AND ep.deleted_at IS NULL
                ORDER BY ep.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['expense_id' => $expenseId]);
        return $statement->fetchAll();
    }

    public function createForExpense(array $data): int
    {
        if (!$this->supportsCreditTracking()) {
            throw new RuntimeException('La base de donnees doit etre migree avant d enregistrer un reglement de depense.');
        }

        $this->db->beginTransaction();

        try {
            $expenseModel = new Expense();
            $expense = $expenseModel->find((int) $data['expense_id']);

            if (!$expense) {
                throw new RuntimeException('Dépense introuvable pour règlement.');
            }

            if (in_array($expense['payment_status'], ['paid'], true) || (float) $expense['balance_due'] <= 0) {
                throw new RuntimeException('Cette dette est déjà soldée.');
            }

            if ((float) $data['amount'] <= 0 || (float) $data['amount'] > (float) $expense['balance_due']) {
                throw new RuntimeException('Montant de règlement invalide.');
            }

            if (($data['method'] ?? '') === 'credit') {
                throw new RuntimeException('Le mode de règlement final ne peut pas être crédit.');
            }

            $data['payment_number'] = $data['payment_number'] ?? (new NumberSequence())->next('expense_payment');

            $statement = $this->db->prepare('INSERT INTO expense_payments (expense_id, payment_number, payment_date, amount, method, reference, notes, recorded_by, deleted_at, created_at, updated_at)
                VALUES (:expense_id, :payment_number, :payment_date, :amount, :method, :reference, :notes, :recorded_by, NULL, NOW(), NOW())');
            $statement->execute($data);

            $paymentId = (int) $this->db->lastInsertId();
            $expenseModel->refreshPaymentStatus((int) $data['expense_id']);

            $this->db->commit();
            return $paymentId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    private function supportsCreditTracking(): bool
    {
        $columnStatement = $this->db->query("SHOW COLUMNS FROM expenses LIKE 'payment_status'");
        $paymentsTableStatement = $this->db->query("SHOW TABLES LIKE 'expense_payments'");

        return (bool) $columnStatement->fetch() && (bool) $paymentsTableStatement->fetch();
    }
}