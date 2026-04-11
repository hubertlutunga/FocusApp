<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;
use Throwable;

final class ProcurementPayment extends Model
{
    public function byProcurement(int $procurementId): array
    {
        if (!$this->supportsCreditTracking()) {
            return [];
        }

        $sql = 'SELECT pp.*, u.full_name AS user_name
                FROM procurement_payments pp
                INNER JOIN users u ON u.id = pp.recorded_by
                WHERE pp.procurement_id = :procurement_id AND pp.deleted_at IS NULL
                ORDER BY pp.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['procurement_id' => $procurementId]);
        return $statement->fetchAll();
    }

    public function createForProcurement(array $data): int
    {
        if (!$this->supportsCreditTracking()) {
            throw new RuntimeException('La base de donnees doit etre migree avant d enregistrer un reglement fournisseur.');
        }

        $this->db->beginTransaction();

        try {
            $procurementModel = new Procurement();
            $procurement = $procurementModel->find((int) $data['procurement_id']);

            if (!$procurement) {
                throw new RuntimeException('Approvisionnement introuvable pour règlement.');
            }

            if ((float) $procurement['balance_due'] <= 0 || ($procurement['payment_status'] ?? '') === 'paid') {
                throw new RuntimeException('Cette dette fournisseur est déjà soldée.');
            }

            if ((float) $data['amount'] <= 0 || (float) $data['amount'] > (float) $procurement['balance_due']) {
                throw new RuntimeException('Montant de règlement invalide.');
            }

            if (($data['method'] ?? '') === 'credit') {
                throw new RuntimeException('Le règlement final ne peut pas être marqué à crédit.');
            }

            $statement = $this->db->prepare('INSERT INTO procurement_payments (procurement_id, payment_number, payment_date, amount, method, reference, notes, recorded_by, deleted_at, created_at, updated_at)
                VALUES (:procurement_id, :payment_number, :payment_date, :amount, :method, :reference, :notes, :recorded_by, NULL, NOW(), NOW())');
            $statement->execute($data);

            $paymentId = (int) $this->db->lastInsertId();
            $procurementModel->refreshPaymentStatus((int) $data['procurement_id']);

            $this->db->commit();
            return $paymentId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    private function supportsCreditTracking(): bool
    {
        $tableStatement = $this->db->query("SHOW TABLES LIKE 'procurement_payments'");
        return (bool) $tableStatement->fetch();
    }
}