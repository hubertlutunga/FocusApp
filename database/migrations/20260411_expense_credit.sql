ALTER TABLE expenses
    MODIFY payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'card', 'cheque', 'other', 'credit') NOT NULL DEFAULT 'cash',
    ADD COLUMN payment_status ENUM('unpaid', 'partial_paid', 'paid') NOT NULL DEFAULT 'paid' AFTER payment_method,
    ADD COLUMN amount_paid DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER payment_status,
    ADD COLUMN balance_due DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER amount_paid,
    ADD COLUMN settled_at DATETIME NULL AFTER balance_due;

UPDATE expenses
SET amount_paid = amount,
    balance_due = 0,
    payment_status = 'paid',
    settled_at = created_at
WHERE payment_method <> 'credit';

UPDATE expenses
SET amount_paid = 0,
    balance_due = amount,
    payment_status = 'unpaid',
    settled_at = NULL
WHERE payment_method = 'credit';

CREATE TABLE expense_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_id BIGINT UNSIGNED NOT NULL,
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
    CONSTRAINT fk_expense_payments_expense FOREIGN KEY (expense_id) REFERENCES expenses(id),
    CONSTRAINT fk_expense_payments_user FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO number_sequences (document_type, prefix, last_number, padding, fiscal_year)
SELECT 'expense_payment', 'REG', 1, 5, YEAR(CURDATE())
WHERE NOT EXISTS (
    SELECT 1 FROM number_sequences WHERE document_type = 'expense_payment'
);

INSERT INTO expense_payments (expense_id, payment_number, payment_date, amount, method, notes, recorded_by)
SELECT id, CONCAT('REG-', YEAR(expense_date), '-', LPAD(id, 5, '0')), expense_date, amount, payment_method, 'Règlement initial migré', created_by
FROM expenses
WHERE payment_status = 'paid';

UPDATE number_sequences
SET last_number = GREATEST(last_number, (SELECT COALESCE(COUNT(*), 0) FROM expense_payments))
WHERE document_type = 'expense_payment';