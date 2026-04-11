ALTER TABLE procurements
    ADD COLUMN payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'card', 'cheque', 'other', 'credit') NOT NULL DEFAULT 'cash' AFTER status,
    ADD COLUMN payment_status ENUM('unpaid', 'partial_paid', 'paid') NOT NULL DEFAULT 'paid' AFTER payment_method,
    ADD COLUMN amount_paid DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER payment_status,
    ADD COLUMN balance_due DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER amount_paid,
    ADD COLUMN settled_at DATETIME NULL AFTER balance_due;

UPDATE procurements
SET amount_paid = grand_total,
    balance_due = 0,
    payment_status = 'paid',
    settled_at = created_at
WHERE payment_method <> 'credit';

UPDATE procurements
SET amount_paid = 0,
    balance_due = grand_total,
    payment_status = 'unpaid',
    settled_at = NULL
WHERE payment_method = 'credit';

CREATE TABLE procurement_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    procurement_id BIGINT UNSIGNED NOT NULL,
    payment_number VARCHAR(30) NOT NULL UNIQUE,
    payment_date DATE NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    method ENUM('cash', 'mobile_money', 'bank_transfer', 'card', 'cheque', 'other') NOT NULL DEFAULT 'cash',
    reference VARCHAR(100) NULL,
    notes TEXT NULL,
    recorded_by BIGINT UNSIGNED NOT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_procurement_payments_procurement FOREIGN KEY (procurement_id) REFERENCES procurements(id),
    CONSTRAINT fk_procurement_payments_user FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO number_sequences (document_type, prefix, last_number, padding, fiscal_year)
SELECT 'procurement_payment', 'RAP', 1, 5, YEAR(CURDATE())
WHERE NOT EXISTS (
    SELECT 1 FROM number_sequences WHERE document_type = 'procurement_payment'
);

INSERT INTO procurement_payments (procurement_id, payment_number, payment_date, amount, method, notes, recorded_by)
SELECT id, CONCAT('RAP-', YEAR(procurement_date), '-', LPAD(id, 5, '0')), procurement_date, grand_total, payment_method, 'Règlement initial migré', user_id
FROM procurements
WHERE payment_status = 'paid';

UPDATE number_sequences
SET last_number = GREATEST(last_number, (SELECT COALESCE(COUNT(*), 0) FROM procurement_payments))
WHERE document_type = 'procurement_payment';