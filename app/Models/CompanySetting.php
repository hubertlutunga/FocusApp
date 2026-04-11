<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CompanySetting extends Model
{
    public function first(): ?array
    {
        $statement = $this->db->query('SELECT * FROM company_settings ORDER BY id ASC LIMIT 1');
        $settings = $statement->fetch();
        return $settings ?: null;
    }

    public function updateSettings(int $id, array $data): void
    {
        $this->db->beginTransaction();

        try {
            $sql = 'UPDATE company_settings SET
                        company_name = :company_name,
                        legal_name = :legal_name,
                        email = :email,
                        phone = :phone,
                        whatsapp = :whatsapp,
                        address = :address,
                        city = :city,
                        country = :country,
                        website = :website,
                        tax_id = :tax_id,
                        idnat = :idnat,
                        commerce_register = :commerce_register,
                        currency_code = :currency_code,
                        quote_prefix = :quote_prefix,
                        invoice_prefix = :invoice_prefix,
                        payment_prefix = :payment_prefix,
                        procurement_prefix = :procurement_prefix,
                        expense_prefix = :expense_prefix,
                        updated_at = NOW()
                    WHERE id = :id';

            $statement = $this->db->prepare($sql);
            $statement->execute([
                'id' => $id,
                'company_name' => $data['company_name'],
                'legal_name' => $data['legal_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'whatsapp' => $data['whatsapp'],
                'address' => $data['address'],
                'city' => $data['city'],
                'country' => $data['country'],
                'website' => $data['website'],
                'tax_id' => $data['tax_id'],
                'idnat' => $data['idnat'],
                'commerce_register' => $data['commerce_register'],
                'currency_code' => $data['currency_code'],
                'quote_prefix' => $data['quote_prefix'],
                'invoice_prefix' => $data['invoice_prefix'],
                'payment_prefix' => $data['payment_prefix'],
                'procurement_prefix' => $data['procurement_prefix'],
                'expense_prefix' => $data['expense_prefix'],
            ]);

            $prefixes = [
                'quote' => $data['quote_prefix'],
                'invoice' => $data['invoice_prefix'],
                'payment' => $data['payment_prefix'],
                'procurement' => $data['procurement_prefix'],
                'expense' => $data['expense_prefix'],
            ];

            $sequenceStatement = $this->db->prepare('UPDATE number_sequences SET prefix = :prefix, updated_at = NOW() WHERE document_type = :document_type');
            foreach ($prefixes as $documentType => $prefix) {
                $sequenceStatement->execute([
                    'prefix' => $prefix,
                    'document_type' => $documentType,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $throwable;
        }
    }
}
