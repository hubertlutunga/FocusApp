<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ActivityLog extends Model
{
    public function log(string $action, string $description, string $module, ?int $userId = null): void
    {
        $statement = $this->db->prepare('INSERT INTO activity_logs (user_id, module, action, description, ip_address, user_agent, created_at) VALUES (:user_id, :module, :action, :description, :ip_address, :user_agent, NOW())');
        $statement->execute([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI', 0, 255),
        ]);
    }

    public function latest(int $limit = 10): array
    {
        $statement = $this->db->prepare('SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.id DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function all(?string $module = null, ?string $action = null): array
    {
        $sql = 'SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id WHERE 1=1';
        $params = [];

        if ($module !== null && $module !== '') {
            $sql .= ' AND al.module = :module';
            $params['module'] = $module;
        }

        if ($action !== null && $action !== '') {
            $sql .= ' AND al.action = :action';
            $params['action'] = $action;
        }

        $sql .= ' ORDER BY al.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function modules(): array
    {
        $statement = $this->db->query('SELECT DISTINCT module FROM activity_logs ORDER BY module ASC');
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function actions(): array
    {
        $statement = $this->db->query('SELECT DISTINCT action FROM activity_logs ORDER BY action ASC');
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
