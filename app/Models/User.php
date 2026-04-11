<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function all(): array
    {
        $sql = 'SELECT u.id, u.role_id, u.full_name, u.email, u.phone, u.is_active, u.last_login_at, u.created_at,
                       r.name AS role_name, r.code AS role_code
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.deleted_at IS NULL
                ORDER BY u.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function findActiveByEmail(string $email): ?array
    {
        $sql = 'SELECT u.id, u.role_id, u.full_name, u.email, u.password, u.phone, u.is_active, r.name AS role_name, r.code AS role_code
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.email = :email AND u.deleted_at IS NULL AND u.is_active = 1
                LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT u.id, u.role_id, u.full_name, u.email, u.phone, u.is_active, r.name AS role_name, r.code AS role_code
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.id = :id AND u.deleted_at IS NULL
                LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function roleOptions(): array
    {
        $statement = $this->db->query('SELECT id, code, name FROM roles ORDER BY name ASC');
        return $statement->fetchAll();
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND deleted_at IS NULL';
        $params = ['email' => $email];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function createUser(array $data): void
    {
        $sql = 'INSERT INTO users (role_id, full_name, email, phone, password, is_active, created_at, updated_at)
                VALUES (:role_id, :full_name, :email, :phone, :password, :is_active, NOW(), NOW())';

        $statement = $this->db->prepare($sql);
        $statement->execute($data);
    }

    public function updateUser(int $id, array $data): void
    {
        $fields = [
            'role_id = :role_id',
            'full_name = :full_name',
            'email = :email',
            'phone = :phone',
            'is_active = :is_active',
            'updated_at = NOW()',
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
        } else {
            unset($data['password']);
        }

        $data['id'] = $id;

        $statement = $this->db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id AND deleted_at IS NULL');
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function updateLastLogin(int $id): void
    {
        $statement = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
