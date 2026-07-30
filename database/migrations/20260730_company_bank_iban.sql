DELIMITER $$

DROP PROCEDURE IF EXISTS add_company_iban_if_missing $$
CREATE PROCEDURE add_company_iban_if_missing()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'company_settings'
          AND COLUMN_NAME = 'iban'
    ) THEN
        ALTER TABLE company_settings ADD COLUMN iban VARCHAR(100) NULL AFTER swift_code;
    END IF;
END $$

DELIMITER ;

CALL add_company_iban_if_missing();

DROP PROCEDURE IF EXISTS add_company_iban_if_missing;
