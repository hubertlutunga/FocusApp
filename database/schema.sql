-- =========================================================
-- Focus Group ERP
-- Schéma MySQL complet : facturation, stock, services, paiements
-- =========================================================

CREATE DATABASE IF NOT EXISTS focus_group CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE focus_group;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS expense_categories;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS quote_items;
DROP TABLE IF EXISTS quotes;
DROP TABLE IF EXISTS procurement_items;
DROP TABLE IF EXISTS procurements;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS company_settings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS number_sequences;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- Référentiels sécurité
-- =========================================================

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE company_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    legal_name VARCHAR(150) NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    whatsapp VARCHAR(50) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    website VARCHAR(150) NULL,
    tax_id VARCHAR(100) NULL,
    idnat VARCHAR(100) NULL,
    commerce_register VARCHAR(100) NULL,
    logo VARCHAR(255) NULL,
    currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
    quote_prefix VARCHAR(20) NOT NULL DEFAULT 'DEV',
    invoice_prefix VARCHAR(20) NOT NULL DEFAULT 'FAC',
    payment_prefix VARCHAR(20) NOT NULL DEFAULT 'PAY',
    procurement_prefix VARCHAR(20) NOT NULL DEFAULT 'APP',
    expense_prefix VARCHAR(20) NOT NULL DEFAULT 'DEP',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- Tiers et catalogue
-- =========================================================

CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_code VARCHAR(30) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    tax_number VARCHAR(100) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(30) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('product', 'service', 'mixed') NOT NULL DEFAULT 'product',
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categories_type_name (type, name)
) ENGINE=InnoDB;

CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_units_symbol (symbol)
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    barcode VARCHAR(100) NULL,
    cost_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    minimum_stock DECIMAL(18,2) NOT NULL DEFAULT 0,
    current_stock DECIMAL(18,2) NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_products_unit FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB;

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    estimated_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
    duration_hours DECIMAL(10,2) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_services_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

-- =========================================================
-- Stock et approvisionnements
-- =========================================================

CREATE TABLE procurements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    procurement_number VARCHAR(30) NOT NULL UNIQUE,
    procurement_date DATE NOT NULL,
    expected_date DATE NULL,
    received_date DATE NULL,
    status ENUM('draft', 'ordered', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_procurements_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_procurements_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE procurement_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    procurement_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(18,2) NOT NULL,
    unit_cost DECIMAL(18,2) NOT NULL,
    line_total DECIMAL(18,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_procurement_items_procurement FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    CONSTRAINT fk_procurement_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    movement_type ENUM('entry', 'exit', 'adjustment', 'invoice_validation', 'invoice_cancellation', 'procurement_receipt', 'manual') NOT NULL,
    quantity DECIMAL(18,2) NOT NULL,
    quantity_before DECIMAL(18,2) NOT NULL DEFAULT 0,
    quantity_after DECIMAL(18,2) NOT NULL DEFAULT 0,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    note TEXT NULL,
    movement_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_stock_movements_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- =========================================================
-- Devis et facturation
-- =========================================================

CREATE TABLE quotes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    quote_number VARCHAR(30) NOT NULL UNIQUE,
    quote_date DATE NOT NULL,
    valid_until DATE NULL,
    status ENUM('draft', 'sent', 'approved', 'rejected', 'converted', 'cancelled') NOT NULL DEFAULT 'draft',
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotes_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_quotes_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE quote_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('product', 'service') NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    service_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(18,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    line_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quote_items_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    CONSTRAINT fk_quote_items_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_quote_items_service FOREIGN KEY (service_id) REFERENCES services(id)
) ENGINE=InnoDB;

CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    status ENUM('draft', 'validated', 'partial_paid', 'paid', 'cancelled') NOT NULL DEFAULT 'draft',
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    amount_paid DECIMAL(18,2) NOT NULL DEFAULT 0,
    balance_due DECIMAL(18,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    validated_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    validated_by BIGINT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_quote FOREIGN KEY (quote_id) REFERENCES quotes(id),
    CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_invoices_validated_by FOREIGN KEY (validated_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('product', 'service') NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    service_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(18,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    line_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_items_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_invoice_items_service FOREIGN KEY (service_id) REFERENCES services(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    payment_number VARCHAR(30) NOT NULL UNIQUE,
    payment_date DATE NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    method ENUM('cash', 'mobile_money', 'bank_transfer', 'card', 'cheque', 'other') NOT NULL DEFAULT 'cash',
    reference VARCHAR(100) NULL,
    notes TEXT NULL,
    received_by BIGINT UNSIGNED NOT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    CONSTRAINT fk_payments_user FOREIGN KEY (received_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- =========================================================
-- Dépenses et séquences
-- =========================================================

CREATE TABLE expense_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_category_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NULL,
    expense_number VARCHAR(30) NOT NULL UNIQUE,
    expense_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'card', 'cheque', 'other') NOT NULL DEFAULT 'cash',
    created_by BIGINT UNSIGNED NOT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_expenses_category FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id),
    CONSTRAINT fk_expenses_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_expenses_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE number_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type VARCHAR(50) NOT NULL UNIQUE,
    prefix VARCHAR(20) NOT NULL,
    last_number BIGINT UNSIGNED NOT NULL DEFAULT 0,
    padding INT UNSIGNED NOT NULL DEFAULT 5,
    fiscal_year YEAR NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    module VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =========================================================
-- Jeux de données de démarrage
-- =========================================================

INSERT INTO roles (code, name, description) VALUES
('administrateur', 'Administrateur', 'Accès complet au système'),
('caisse', 'Profil caisse', 'Ventes, devis, dépenses, paiements et rapports'),
('gestionnaire_stock', 'Gestionnaire de stock', 'Gestion des produits, approvisionnements et mouvements');

INSERT INTO users (role_id, full_name, email, phone, password, is_active, last_login_at) VALUES
(1, 'Administrateur Focus Group', 'admin@focusgroup.local', '+243 900 000 001', '$2y$12$85oD4.QCIXOqyr8sm5aP4ejZYUJCfonzhhTrm/Nqq6mCjQ3skEpZW', 1, NOW()),
(2, 'Caissier Démonstration', 'caisse@focusgroup.local', '+243 900 000 002', '$2y$12$85oD4.QCIXOqyr8sm5aP4ejZYUJCfonzhhTrm/Nqq6mCjQ3skEpZW', 1, NULL),
(3, 'Gestionnaire Stock Démonstration', 'stock@focusgroup.local', '+243 900 000 003', '$2y$12$85oD4.QCIXOqyr8sm5aP4ejZYUJCfonzhhTrm/Nqq6mCjQ3skEpZW', 1, NULL);

INSERT INTO company_settings (
    company_name, legal_name, email, phone, whatsapp, address, city, country, website, tax_id, idnat, commerce_register, currency_code,
    quote_prefix, invoice_prefix, payment_prefix, procurement_prefix, expense_prefix
) VALUES (
    'Focus Group',
    'Focus Group SARL',
    'contact@focusgroup.cd',
    '+243 900 100 100',
    '+243 900 100 100',
    'Kinshasa, Gombe',
    'Kinshasa',
    'RDC',
    'https://www.focusgroup.cd',
    'A123456789',
    '01-83-N12345X',
    'CD/KIN/RCCM/2026-A-001',
    'USD',
    'DEV',
    'FAC',
    'PAY',
    'APP',
    'DEP'
);

INSERT INTO clients (client_code, company_name, contact_name, phone, email, address, city, notes) VALUES
('CLI-0001', 'Université Horizon', 'Jean Mboyo', '+243 820 111 111', 'achats@horizon.cd', 'Kinshasa / Gombe', 'Kinshasa', 'Client institutionnel'),
('CLI-0002', 'Cabinet Vision Tech', 'Grace Ilunga', '+243 850 222 222', 'direction@visiontech.cd', 'Kinshasa / Limete', 'Kinshasa', 'Maintenance parc informatique');

INSERT INTO suppliers (supplier_code, company_name, contact_name, phone, email, address, city, notes) VALUES
('FOU-0001', 'IT Distribution RDC', 'Patrick Lemba', '+243 810 333 333', 'supply@itdrc.cd', 'Kinshasa / Ngaliema', 'Kinshasa', 'Fournisseur consommables'),
('FOU-0002', 'SecureNet Afrique', 'Sarah Kanku', '+243 890 444 444', 'sales@securenet.africa', 'Kinshasa / Gombe', 'Kinshasa', 'Équipements sécurité');

INSERT INTO categories (type, name, description) VALUES
('product', 'Consommables informatiques', 'Cartouches, câbles, disques, accessoires'),
('product', 'Équipements réseau', 'Switches, routeurs, points d’accès'),
('service', 'Sécurité électronique', 'Installation caméras et contrôle d’accès'),
('service', 'Connectivité', 'Installation Starlink et réseau'),
('service', 'Maintenance informatique', 'Support et maintenance préventive');

INSERT INTO units (name, symbol) VALUES
('Pièce', 'pc'),
('Paquet', 'pkt'),
('Mètre', 'm'),
('Heure', 'h'),
('Forfait', 'forfait');

INSERT INTO products (category_id, unit_id, sku, name, description, barcode, cost_price, sale_price, minimum_stock, current_stock, is_active) VALUES
(1, 1, 'PDT-TONER-001', 'Toner HP 85A', 'Toner noir pour imprimantes HP LaserJet', '100000000001', 65.00, 95.00, 5, 18, 1),
(2, 1, 'PDT-SWITCH-008', 'Switch 8 ports Gigabit', 'Switch réseau 8 ports pour petites infrastructures', '100000000002', 38.00, 55.00, 3, 10, 1),
(1, 1, 'PDT-CABLE-005', 'Câble HDMI 5m', 'Câble HDMI haute vitesse 5 mètres', '100000000003', 6.00, 12.00, 10, 45, 1);

INSERT INTO services (category_id, code, name, description, unit_price, estimated_cost, duration_hours, is_active) VALUES
(3, 'SRV-CAM-001', 'Installation caméras de surveillance', 'Étude, pose, configuration et mise en service', 500.00, 320.00, 6, 1),
(4, 'SRV-STAR-001', 'Installation Starlink', 'Installation antenne, configuration réseau et test', 350.00, 210.00, 4, 1),
(5, 'SRV-MAINT-001', 'Maintenance informatique', 'Maintenance curative et préventive des équipements', 80.00, 30.00, 2, 1),
(4, 'SRV-NET-001', 'Configuration réseau', 'Structuration LAN/WAN, VLAN et routeurs', 220.00, 90.00, 3, 1);

INSERT INTO number_sequences (document_type, prefix, last_number, padding, fiscal_year) VALUES
('quote', 'DEV', 1, 5, 2026),
('invoice', 'FAC', 1, 5, 2026),
('payment', 'PAY', 1, 5, 2026),
('procurement', 'APP', 1, 5, 2026),
('expense', 'DEP', 1, 5, 2026),
('client', 'CLI', 2, 4, 2026),
('supplier', 'FOU', 2, 4, 2026),
('product', 'PDT', 3, 4, 2026),
('service', 'SRV', 4, 4, 2026);

INSERT INTO expense_categories (name, description) VALUES
('Transport', 'Frais de déplacement et logistique'),
('Internet', 'Abonnements internet et connectivité'),
('Énergie', 'Électricité et carburant'),
('Sous-traitance', 'Prestations techniques externes');

INSERT INTO procurements (supplier_id, user_id, procurement_number, procurement_date, expected_date, received_date, status, subtotal, discount_amount, tax_amount, grand_total, notes) VALUES
(1, 3, 'APP-2026-00001', '2026-03-01', '2026-03-03', '2026-03-03', 'received', 1300.00, 0.00, 0.00, 1300.00, 'Approvisionnement initial de consommables'),
(2, 3, 'APP-2026-00002', '2026-03-05', '2026-03-06', '2026-03-06', 'received', 380.00, 0.00, 0.00, 380.00, 'Réception de switches réseau');

INSERT INTO procurement_items (procurement_id, product_id, quantity, unit_cost, line_total) VALUES
(1, 1, 20, 65.00, 1300.00),
(2, 2, 10, 38.00, 380.00);

INSERT INTO quotes (client_id, quote_number, quote_date, valid_until, status, subtotal, discount_amount, tax_amount, grand_total, notes, created_by) VALUES
(1, 'DEV-2026-00001', '2026-03-10', '2026-03-20', 'converted', 690.00, 0.00, 0.00, 690.00, 'Devis mixte produits et services', 1);

INSERT INTO quote_items (quote_id, item_type, product_id, service_id, description, quantity, unit_price, discount_amount, tax_amount, line_total) VALUES
(1, 'product', 1, NULL, 'Toner HP 85A', 2, 95.00, 0.00, 0.00, 190.00),
(1, 'service', NULL, 1, 'Installation caméras de surveillance', 1, 500.00, 0.00, 0.00, 500.00);

INSERT INTO invoices (quote_id, client_id, invoice_number, invoice_date, due_date, status, subtotal, discount_amount, tax_amount, grand_total, amount_paid, balance_due, notes, validated_at, created_by, validated_by) VALUES
(1, 1, 'FAC-2026-00001', '2026-03-12', '2026-03-19', 'partial_paid', 690.00, 0.00, 0.00, 690.00, 300.00, 390.00, 'Facture issue du devis DEV-2026-00001', '2026-03-12 09:30:00', 1, 1);

INSERT INTO invoice_items (invoice_id, item_type, product_id, service_id, description, quantity, unit_price, discount_amount, tax_amount, line_total) VALUES
(1, 'product', 1, NULL, 'Toner HP 85A', 2, 95.00, 0.00, 0.00, 190.00),
(1, 'service', NULL, 1, 'Installation caméras de surveillance', 1, 500.00, 0.00, 0.00, 500.00);

INSERT INTO payments (invoice_id, payment_number, payment_date, amount, method, reference, notes, received_by) VALUES
(1, 'PAY-2026-00001', '2026-03-12', 300.00, 'mobile_money', 'MOMO-987654', 'Acompte reçu à la validation', 2);

INSERT INTO expenses (expense_category_id, supplier_id, expense_number, expense_date, description, amount, payment_method, created_by) VALUES
(2, NULL, 'DEP-2026-00001', '2026-03-02', 'Abonnement internet siège', 120.00, 'bank_transfer', 5),
(1, NULL, 'DEP-2026-00002', '2026-03-08', 'Transport matériel vers client', 45.00, 'cash', 5);

INSERT INTO stock_movements (product_id, movement_type, quantity, quantity_before, quantity_after, reference_type, reference_id, note, movement_date, created_by) VALUES
(1, 'procurement_receipt', 20, 0, 20, 'procurement', 1, 'Réception approvisionnement initial', '2026-03-03 10:00:00', 3),
(2, 'procurement_receipt', 10, 0, 10, 'procurement', 2, 'Réception switches réseau', '2026-03-06 11:00:00', 3),
(1, 'invoice_validation', -2, 20, 18, 'invoice', 1, 'Sortie stock après validation facture FAC-2026-00001', '2026-03-12 09:35:00', 1);

INSERT INTO activity_logs (user_id, module, action, description, ip_address, user_agent, created_at) VALUES
(1, 'authentification', 'login', 'Connexion initiale administrateur', '127.0.0.1', 'Seeder', '2026-03-12 08:00:00'),
(3, 'approvisionnements', 'create', 'Approvisionnement APP-2026-00001 enregistré', '127.0.0.1', 'Seeder', '2026-03-03 10:05:00'),
(1, 'factures', 'validate', 'Facture FAC-2026-00001 validée avec impact stock', '127.0.0.1', 'Seeder', '2026-03-12 09:40:00'),
(2, 'paiements', 'create', 'Paiement PAY-2026-00001 reçu sur FAC-2026-00001', '127.0.0.1', 'Seeder', '2026-03-12 09:45:00');
