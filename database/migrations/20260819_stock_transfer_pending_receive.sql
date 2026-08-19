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
    'stock_transfers',
    'status',
    'ALTER TABLE stock_transfers ADD COLUMN status ENUM(''pending'', ''received'', ''cancelled'') NOT NULL DEFAULT ''pending'' AFTER transfer_type'
);

CALL add_column_if_missing(
    'stock_transfers',
    'requested_at',
    'ALTER TABLE stock_transfers ADD COLUMN requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER note'
);

CALL add_column_if_missing(
    'stock_transfers',
    'received_at',
    'ALTER TABLE stock_transfers ADD COLUMN received_at DATETIME NULL AFTER requested_at'
);

CALL add_column_if_missing(
    'stock_transfers',
    'received_by',
    'ALTER TABLE stock_transfers ADD COLUMN received_by BIGINT UNSIGNED NULL AFTER received_at'
);

SET @fk_received_by_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'stock_transfers'
      AND CONSTRAINT_NAME = 'fk_stock_transfers_received_by'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_received_by_exists = 0,
    'ALTER TABLE stock_transfers ADD CONSTRAINT fk_stock_transfers_received_by FOREIGN KEY (received_by) REFERENCES users(id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE stock_transfers
SET status = 'received',
    requested_at = COALESCE(requested_at, created_at),
    received_at = COALESCE(received_at, created_at)
WHERE status IS NULL OR status = '';

DROP PROCEDURE IF EXISTS add_column_if_missing;
