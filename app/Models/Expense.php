<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

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

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT * FROM expenses WHERE id = :id AND deleted_at IS NULL LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $expense = $statement->fetch();
        return $expense ?: null;
    }

    public function create(array $data): void
    {
        $sql = 'INSERT INTO expenses (expense_category_id, supplier_id, expense_number, expense_date, description, amount, payment_method, created_by, deleted_at, created_at, updated_at)
                VALUES (:expense_category_id, :supplier_id, :expense_number, :expense_date, :description, :amount, :payment_method, :created_by, NULL, NOW(), NOW())';
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function updateExpense(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE expenses SET expense_category_id = :expense_category_id, supplier_id = :supplier_id, expense_date = :expense_date, description = :description, amount = :amount, payment_method = :payment_method, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE expenses SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }
}
