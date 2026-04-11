<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

final class Payment extends Model
{
    public function all(): array
    {
        $sql = 'SELECT p.*, i.invoice_number, c.company_name AS client_name, u.full_name AS user_name
                FROM payments p
                INNER JOIN invoices i ON i.id = p.invoice_id
                INNER JOIN clients c ON c.id = i.client_id
                INNER JOIN users u ON u.id = p.received_by
                WHERE p.deleted_at IS NULL
                ORDER BY p.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function byInvoice(int $invoiceId): array
    {
        $sql = 'SELECT p.*, u.full_name AS user_name
                FROM payments p
                INNER JOIN users u ON u.id = p.received_by
                WHERE p.invoice_id = :invoice_id AND p.deleted_at IS NULL
                ORDER BY p.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['invoice_id' => $invoiceId]);
        return $statement->fetchAll();
    }

    public function createForInvoice(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $invoiceModel = new Invoice();
            $invoice = $invoiceModel->find((int) $data['invoice_id']);
            if (!$invoice || $invoice['status'] === 'cancelled' || !in_array($invoice['status'], ['validated', 'partial_paid'], true)) {
                throw new \RuntimeException('Facture invalide pour paiement.');
            }

            $remaining = (float) $invoice['balance_due'];
            if ((float) $data['amount'] <= 0 || (float) $data['amount'] > $remaining) {
                throw new \RuntimeException('Montant de paiement invalide.');
            }

            $statement = $this->db->prepare('INSERT INTO payments (invoice_id, payment_number, payment_date, amount, method, reference, notes, received_by, deleted_at, created_at, updated_at)
                VALUES (:invoice_id, :payment_number, :payment_date, :amount, :method, :reference, :notes, :received_by, NULL, NOW(), NOW())');
            $statement->execute($data);
            $paymentId = (int) $this->db->lastInsertId();

            $invoiceModel->refreshPaymentStatus((int) $data['invoice_id']);

            $this->db->commit();
            return $paymentId;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }
}
