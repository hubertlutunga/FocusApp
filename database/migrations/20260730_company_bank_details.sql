DELIMITER $$

DROP PROCEDURE IF EXISTS add_company_column_if_missing $$
CREATE PROCEDURE add_company_column_if_missing(
    IN p_column_name VARCHAR(64),
    IN p_alter_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'company_settings'
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @sql = p_alter_sql;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$

DELIMITER ;

CALL add_company_column_if_missing('bank_name', 'ALTER TABLE company_settings ADD COLUMN bank_name VARCHAR(150) NULL AFTER commerce_register');
CALL add_company_column_if_missing('bank_account_usd', 'ALTER TABLE company_settings ADD COLUMN bank_account_usd VARCHAR(100) NULL AFTER bank_name');
CALL add_company_column_if_missing('bank_account_cdf', 'ALTER TABLE company_settings ADD COLUMN bank_account_cdf VARCHAR(100) NULL AFTER bank_account_usd');
CALL add_company_column_if_missing('swift_code', 'ALTER TABLE company_settings ADD COLUMN swift_code VARCHAR(50) NULL AFTER bank_account_cdf');

DROP PROCEDURE IF EXISTS add_company_column_if_missing;
