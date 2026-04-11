<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Unit extends Model
{
    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM units WHERE deleted_at IS NULL ORDER BY name ASC');
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM units WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $unit = $statement->fetch();
        return $unit ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare('INSERT INTO units (name, symbol, created_at, updated_at) VALUES (:name, :symbol, NOW(), NOW())');
        $statement->execute($data);
    }

    public function updateUnit(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE units SET name = :name, symbol = :symbol, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE units SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function options(): array
    {
        $statement = $this->db->query('SELECT id, name, symbol FROM units WHERE deleted_at IS NULL ORDER BY name ASC');
        return $statement->fetchAll();
    }
}
