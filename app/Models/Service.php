<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Service extends Model
{
    public function all(): array
    {
        $sql = 'SELECT s.*, c.name AS category_name
                FROM services s
                INNER JOIN categories c ON c.id = s.category_id
                WHERE s.deleted_at IS NULL
                ORDER BY s.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM services WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $service = $statement->fetch();
        return $service ?: null;
    }

    public function options(): array
    {
        $statement = $this->db->query('SELECT id, code, name, unit_price FROM services WHERE deleted_at IS NULL AND is_active = 1 ORDER BY name ASC');
        return $statement->fetchAll();
    }

    public function create(array $data): void
    {
        $sql = 'INSERT INTO services (category_id, code, name, description, unit_price, estimated_cost, duration_hours, is_active, created_at, updated_at)
                VALUES (:category_id, :code, :name, :description, :unit_price, :estimated_cost, :duration_hours, :is_active, NOW(), NOW())';
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function updateService(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE services SET
                    category_id = :category_id,
                    code = :code,
                    name = :name,
                    description = :description,
                    unit_price = :unit_price,
                    estimated_cost = :estimated_cost,
                    duration_hours = :duration_hours,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL';
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE services SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }
}
