<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Client extends Model
{
    public function all(bool $onlyDebtors = false): array
    {
        $sql = "SELECT c.*, 
                       COALESCE(SUM(CASE 
                           WHEN i.deleted_at IS NULL 
                            AND i.status IN ('validated', 'partial_paid') 
                            AND i.balance_due > 0
                           THEN i.balance_due
                           ELSE 0
                       END), 0) AS outstanding_balance,
                       COALESCE(SUM(CASE 
                           WHEN i.deleted_at IS NULL 
                            AND i.status IN ('validated', 'partial_paid') 
                            AND i.balance_due > 0
                           THEN 1
                           ELSE 0
                       END), 0) AS outstanding_invoice_count
                FROM clients c
                LEFT JOIN invoices i ON i.client_id = c.id
                WHERE c.deleted_at IS NULL
                GROUP BY c.id
                " . ($onlyDebtors ? 'HAVING outstanding_balance > 0 ' : '') . '
                ORDER BY outstanding_balance DESC, c.id DESC';

        $statement = $this->db->query($sql);
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM clients WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $client = $statement->fetch();
        return $client ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare('INSERT INTO clients (client_code, company_name, contact_name, phone, email, address, city, tax_number, notes, is_active, created_at, updated_at) VALUES (:client_code, :company_name, :contact_name, :phone, :email, :address, :city, :tax_number, :notes, :is_active, NOW(), NOW())');
        $statement->execute($data);
    }

    public function updateClient(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE clients SET company_name = :company_name, contact_name = :contact_name, phone = :phone, email = :email, address = :address, city = :city, tax_number = :tax_number, notes = :notes, is_active = :is_active, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE clients SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function options(): array
    {
        $statement = $this->db->query('SELECT id, client_code, company_name, contact_name FROM clients WHERE deleted_at IS NULL AND is_active = 1 ORDER BY company_name ASC');
        return $statement->fetchAll();
    }
}
