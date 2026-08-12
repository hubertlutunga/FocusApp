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

DROP PROCEDURE IF EXISTS make_column_nullable_if_needed $$
CREATE PROCEDURE make_column_nullable_if_needed(
    IN p_table_name VARCHAR(64),
    IN p_column_name VARCHAR(64),
    IN p_alter_sql TEXT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND COLUMN_NAME = p_column_name
          AND IS_NULLABLE = 'NO'
    ) THEN
        SET @sql = p_alter_sql;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$

DELIMITER ;

CALL add_column_if_missing(
    'stock_transfers',
    'source_shop_id',
    'ALTER TABLE stock_transfers ADD COLUMN source_shop_id BIGINT UNSIGNED NULL AFTER product_id'
);

CALL add_column_if_missing(
    'stock_transfers',
    'transfer_type',
    'ALTER TABLE stock_transfers ADD COLUMN transfer_type ENUM(''to_shop'', ''to_central'') NOT NULL DEFAULT ''to_shop'' AFTER destination_shop_id'
);

CALL make_column_nullable_if_needed(
    'stock_transfers',
    'destination_shop_id',
    'ALTER TABLE stock_transfers MODIFY destination_shop_id BIGINT UNSIGNED NULL'
);

SET @fk_source_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'stock_transfers'
      AND CONSTRAINT_NAME = 'fk_stock_transfers_source_shop'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_source_exists = 0,
    'ALTER TABLE stock_transfers ADD CONSTRAINT fk_stock_transfers_source_shop FOREIGN KEY (source_shop_id) REFERENCES shops(id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE stock_transfers
SET transfer_type = 'to_shop'
WHERE transfer_type IS NULL OR transfer_type = '';

DROP PROCEDURE IF EXISTS add_column_if_missing;
DROP PROCEDURE IF EXISTS make_column_nullable_if_needed;
