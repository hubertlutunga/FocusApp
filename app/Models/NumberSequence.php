<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;
use Throwable;

final class NumberSequence extends Model
{
    private const DEFAULT_SEQUENCES = [
        'quote' => ['prefix' => 'DEV', 'padding' => 5, 'table' => 'quotes'],
        'invoice' => ['prefix' => 'FAC', 'padding' => 5, 'table' => 'invoices'],
        'payment' => ['prefix' => 'PAY', 'padding' => 5, 'table' => 'payments'],
        'expense_payment' => ['prefix' => 'REG', 'padding' => 5, 'table' => 'expense_payments'],
        'procurement_payment' => ['prefix' => 'RAP', 'padding' => 5, 'table' => 'procurement_payments'],
        'procurement' => ['prefix' => 'APP', 'padding' => 5, 'table' => 'procurements'],
        'expense' => ['prefix' => 'DEP', 'padding' => 5, 'table' => 'expenses'],
        'client' => ['prefix' => 'CLI', 'padding' => 4, 'table' => 'clients'],
        'supplier' => ['prefix' => 'FOU', 'padding' => 4, 'table' => 'suppliers'],
        'product' => ['prefix' => 'PDT', 'padding' => 4, 'table' => 'products'],
        'service' => ['prefix' => 'SRV', 'padding' => 4, 'table' => 'services'],
    ];

    public function next(string $documentType): string
    {
        $this->db->beginTransaction();

        try {
            $sequence = $this->lockSequence($documentType);

            if (!$sequence) {
                $this->bootstrapSequence($documentType);
                $sequence = $this->lockSequence($documentType);
            }

            if (!$sequence) {
                throw new RuntimeException('Séquence introuvable pour ' . $documentType);
            }

            $nextNumber = (int) $sequence['last_number'] + 1;
            $update = $this->db->prepare('UPDATE number_sequences SET last_number = :last_number, updated_at = NOW() WHERE id = :id');
            $update->execute([
                'last_number' => $nextNumber,
                'id' => $sequence['id'],
            ]);

            $this->db->commit();

            $padding = (int) $sequence['padding'];
            $prefix = (string) $sequence['prefix'];
            $formattedNumber = str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);

            if (in_array($documentType, ['client', 'supplier', 'product', 'service'], true)) {
                return $prefix . '-' . $formattedNumber;
            }

            return $prefix . '-' . date('Y') . '-' . $formattedNumber;
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }

    private function lockSequence(string $documentType): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM number_sequences WHERE document_type = :document_type LIMIT 1 FOR UPDATE');
        $statement->execute(['document_type' => $documentType]);
        return $statement->fetch();
    }

    private function bootstrapSequence(string $documentType): void
    {
        $default = self::DEFAULT_SEQUENCES[$documentType] ?? null;

        if ($default === null) {
            return;
        }

        $lastNumber = $this->resolveInitialLastNumber($default['table']);
        $statement = $this->db->prepare(
            'INSERT INTO number_sequences (document_type, prefix, last_number, padding, fiscal_year)
             SELECT :document_type, :prefix, :last_number, :padding, :fiscal_year
             WHERE NOT EXISTS (
                 SELECT 1 FROM number_sequences WHERE document_type = :document_type_check
             )'
        );
        $statement->execute([
            'document_type' => $documentType,
            'prefix' => $default['prefix'],
            'last_number' => $lastNumber,
            'padding' => $default['padding'],
            'fiscal_year' => (int) date('Y'),
            'document_type_check' => $documentType,
        ]);
    }

    private function resolveInitialLastNumber(string $table): int
    {
        $statement = $this->db->query(sprintf('SHOW TABLES LIKE %s', $this->db->quote($table)));

        if (!$statement->fetch()) {
            return 0;
        }

        $countStatement = $this->db->query(sprintf('SELECT COALESCE(COUNT(*), 0) FROM %s', $table));
        return (int) $countStatement->fetchColumn();
    }
}
