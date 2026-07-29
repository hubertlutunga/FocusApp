UPDATE roles
SET code = 'caissier', name = 'Caissier', description = 'Ventes, devis, factures, paiements et rapports'
WHERE code = 'caisse'
    AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM roles WHERE code = 'caissier') existing_role);

INSERT INTO roles (code, name, description)
SELECT 'caissier', 'Caissier', 'Ventes, devis, factures, paiements et rapports'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'caissier');

CREATE TABLE shops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    manager_name VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE users
    ADD COLUMN shop_id BIGINT UNSIGNED NULL AFTER role_id,
    ADD CONSTRAINT fk_users_shop FOREIGN KEY (shop_id) REFERENCES shops(id);

CREATE TABLE product_stocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    shop_id BIGINT UNSIGNED NOT NULL,
    minimum_stock DECIMAL(18,2) NULL,
    current_stock DECIMAL(18,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_product_stocks_product_shop (product_id, shop_id),
    CONSTRAINT fk_product_stocks_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_product_stocks_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stock_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    destination_shop_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(18,2) NOT NULL,
    note TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_transfers_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_stock_transfers_destination_shop FOREIGN KEY (destination_shop_id) REFERENCES shops(id),
    CONSTRAINT fk_stock_transfers_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

ALTER TABLE stock_movements
    MODIFY movement_type ENUM('entry', 'exit', 'adjustment', 'invoice_validation', 'invoice_cancellation', 'procurement_receipt', 'transfer_out', 'transfer_in', 'manual') NOT NULL,
    ADD COLUMN source_shop_id BIGINT UNSIGNED NULL AFTER quantity_after,
    ADD COLUMN destination_shop_id BIGINT UNSIGNED NULL AFTER source_shop_id,
    ADD CONSTRAINT fk_stock_movements_source_shop FOREIGN KEY (source_shop_id) REFERENCES shops(id),
    ADD CONSTRAINT fk_stock_movements_destination_shop FOREIGN KEY (destination_shop_id) REFERENCES shops(id);

ALTER TABLE invoices
    ADD COLUMN shop_id BIGINT UNSIGNED NULL AFTER quote_id,
    ADD COLUMN currency_code VARCHAR(10) NOT NULL DEFAULT 'USD' AFTER status,
    ADD CONSTRAINT fk_invoices_shop FOREIGN KEY (shop_id) REFERENCES shops(id);