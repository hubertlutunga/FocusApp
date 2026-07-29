<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Shop extends Model
{
    public function all(): array
    {
        $sql = 'SELECT s.*,
                (SELECT COUNT(*) FROM users u WHERE u.shop_id = s.id AND u.deleted_at IS NULL) AS user_count
            FROM shops s
                WHERE s.deleted_at IS NULL
                ORDER BY s.name ASC';

        return $this->db->query($sql)->fetchAll();
    }

    public function options(bool $activeOnly = true): array
    {
        $sql = 'SELECT id, code, name FROM shops WHERE deleted_at IS NULL';
        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY name ASC';

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM shops WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $shop = $statement->fetch();

        return $shop ?: null;
    }

    public function create(array $data): void
    {
        $sql = 'INSERT INTO shops (code, name, manager_name, phone, address, city, is_active, created_at, updated_at)
                VALUES (:code, :name, :manager_name, :phone, :address, :city, :is_active, NOW(), NOW())';

        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function updateShop(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE shops SET
                    code = :code,
                    name = :name,
                    manager_name = :manager_name,
                    phone = :phone,
                    address = :address,
                    city = :city,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL';

        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE shops SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }
}