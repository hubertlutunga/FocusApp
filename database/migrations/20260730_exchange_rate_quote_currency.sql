DELIMITER $$

DROP PROCEDURE IF EXISTS add_column_if_missing $$
CREATE PROCEDURE add_column_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_column_name VARCHAR(64),
    IN p_alter_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @sql = p_alter_sql;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$

DELIMITER ;

CALL add_column_if_missing(
    'company_settings',
    'exchange_rate',
    'ALTER TABLE company_settings ADD COLUMN exchange_rate DECIMAL(18,4) NOT NULL DEFAULT 1.0000 AFTER currency_code'
);

CALL add_column_if_missing(
    'quotes',
    'currency_code',
    'ALTER TABLE quotes ADD COLUMN currency_code VARCHAR(10) NOT NULL DEFAULT ''USD'' AFTER status'
);

UPDATE company_settings
SET exchange_rate = 1.0000
WHERE exchange_rate IS NULL OR exchange_rate < 1;

UPDATE quotes
SET currency_code = 'USD'
WHERE currency_code IS NULL OR currency_code = '';

DROP PROCEDURE IF EXISTS add_column_if_missing;
