<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Supplier extends Model
{
    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM suppliers WHERE deleted_at IS NULL ORDER BY id DESC');
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM suppliers WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $supplier = $statement->fetch();
        return $supplier ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare('INSERT INTO suppliers (supplier_code, company_name, contact_name, phone, email, address, city, notes, is_active, created_at, updated_at) VALUES (:supplier_code, :company_name, :contact_name, :phone, :email, :address, :city, :notes, :is_active, NOW(), NOW())');
        $statement->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function updateSupplier(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE suppliers SET company_name = :company_name, contact_name = :contact_name, phone = :phone, email = :email, address = :address, city = :city, notes = :notes, is_active = :is_active, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE suppliers SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function options(): array
    {
        $statement = $this->db->query('SELECT id, supplier_code, company_name FROM suppliers WHERE deleted_at IS NULL AND is_active = 1 ORDER BY company_name ASC');
        return $statement->fetchAll();
    }
}
