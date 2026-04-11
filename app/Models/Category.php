<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY type ASC, name ASC');
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();
        return $category ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare('INSERT INTO categories (type, name, description, created_at, updated_at) VALUES (:type, :name, :description, NOW(), NOW())');
        $statement->execute($data);
    }

    public function updateCategory(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE categories SET type = :type, name = :name, description = :description, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE categories SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function optionsByType(array $types): array
    {
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $statement = $this->db->prepare("SELECT id, type, name FROM categories WHERE deleted_at IS NULL AND type IN ($placeholders) ORDER BY name ASC");
        $statement->execute($types);
        return $statement->fetchAll();
    }
}
