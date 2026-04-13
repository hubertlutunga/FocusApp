ALTER TABLE quotes
    ADD COLUMN tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER discount_amount;

ALTER TABLE invoices
    ADD COLUMN tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER discount_amount;