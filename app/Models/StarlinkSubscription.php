<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class StarlinkSubscription extends Model
{
    public function all(): array
    {
        $sql = "SELECT ss.*, c.client_code, c.company_name, c.contact_name, c.phone,
                       DATEDIFF(ss.end_date, CURDATE()) AS days_to_expiry
                FROM starlink_subscriptions ss
                INNER JOIN clients c ON c.id = ss.client_id
                WHERE ss.deleted_at IS NULL
                ORDER BY ss.end_date ASC, ss.id DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM starlink_subscriptions WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $subscription = $statement->fetch();

        return $subscription ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare('INSERT INTO starlink_subscriptions (client_id, line_label, subscription_number, plan_name, start_date, end_date, monthly_amount, reminder_days, status, notes, created_by, created_at, updated_at)
            VALUES (:client_id, :line_label, :subscription_number, :plan_name, :start_date, :end_date, :monthly_amount, :reminder_days, :status, :notes, :created_by, NOW(), NOW())');
        $statement->execute($data);
    }

    public function updateSubscription(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE starlink_subscriptions
            SET client_id = :client_id,
                line_label = :line_label,
                subscription_number = :subscription_number,
                plan_name = :plan_name,
                start_date = :start_date,
                end_date = :end_date,
                monthly_amount = :monthly_amount,
                reminder_days = :reminder_days,
                status = :status,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL');
        $statement->execute($data);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE starlink_subscriptions SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
    }

    public function dashboardAlerts(int $limit = 8): array
    {
        $statement = $this->db->prepare("SELECT ss.id, ss.line_label, ss.subscription_number, ss.end_date, ss.status,
                       c.company_name,
                       DATEDIFF(ss.end_date, CURDATE()) AS days_to_expiry
                FROM starlink_subscriptions ss
                INNER JOIN clients c ON c.id = ss.client_id
                WHERE ss.deleted_at IS NULL
                  AND ss.status = 'active'
                  AND ss.end_date <= DATE_ADD(CURDATE(), INTERVAL ss.reminder_days DAY)
                ORDER BY ss.end_date ASC, ss.id DESC
                LIMIT :limit");
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function dashboardOverview(): array
    {
        $statement = $this->db->query("SELECT
                COUNT(*) AS total_subscriptions,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_subscriptions,
                SUM(CASE WHEN status = 'active' AND end_date < CURDATE() THEN 1 ELSE 0 END) AS expired_active_subscriptions,
                SUM(CASE WHEN status = 'active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS expiring_within_7_days
            FROM starlink_subscriptions
            WHERE deleted_at IS NULL");

        $row = $statement->fetch();

        return [
            'total_subscriptions' => (int) ($row['total_subscriptions'] ?? 0),
            'active_subscriptions' => (int) ($row['active_subscriptions'] ?? 0),
            'expired_active_subscriptions' => (int) ($row['expired_active_subscriptions'] ?? 0),
            'expiring_within_7_days' => (int) ($row['expiring_within_7_days'] ?? 0),
        ];
    }
}
