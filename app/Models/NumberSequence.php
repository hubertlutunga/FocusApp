<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;

final class NumberSequence extends Model
{
    public function next(string $documentType): string
    {
        $this->db->beginTransaction();

        $statement = $this->db->prepare('SELECT * FROM number_sequences WHERE document_type = :document_type LIMIT 1 FOR UPDATE');
        $statement->execute(['document_type' => $documentType]);
        $sequence = $statement->fetch();

        if (!$sequence) {
            $this->db->rollBack();
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
    }
}
