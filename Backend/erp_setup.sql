-- ============================================================
-- ERP SYSTEM — MySQL Setup Script
-- ============================================================

CREATE DATABASE IF NOT EXISTS erp_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE erp_db;

-- ============================================================
-- TABEL 1: quantity_units
-- ============================================================
CREATE TABLE IF NOT EXISTS quantity_units (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(50)  NOT NULL,
    name_plural VARCHAR(50)  NULL,
    description TEXT         NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_quantity_units_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 2: product_categories
-- ============================================================
CREATE TABLE IF NOT EXISTS product_categories (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_product_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 3: locations
-- ============================================================
CREATE TABLE IF NOT EXISTS locations (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_locations_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 4: suppliers
-- ============================================================
CREATE TABLE IF NOT EXISTS suppliers (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name         VARCHAR(150)  NOT NULL,
    contact_name VARCHAR(100)  NULL,
    phone        VARCHAR(20)   NULL,
    email        VARCHAR(100)  NULL,
    address      TEXT          NULL,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    notes        TEXT          NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 5: customers
-- ============================================================
CREATE TABLE IF NOT EXISTS customers (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name         VARCHAR(150)  NOT NULL,
    phone        VARCHAR(20)   NULL,
    email        VARCHAR(100)  NULL,
    address      TEXT          NULL,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    notes        TEXT          NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 6: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username    VARCHAR(50)   NOT NULL,
    full_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(100)  NULL,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','manager','staff','cashier') NOT NULL DEFAULT 'staff',
    is_active   TINYINT(1)    NOT NULL DEFAULT 1,
    last_login  DATETIME      NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 7: sessions
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    session_key VARCHAR(64)   NOT NULL,
    user_id     INT UNSIGNED  NOT NULL,
    expires_at  DATETIME      NOT NULL,
    last_used   DATETIME      NULL,
    ip_address  VARCHAR(45)   NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sessions_key (session_key),
    KEY idx_sessions_user (user_id),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 8: products
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    code             VARCHAR(50)      NULL,
    barcode          VARCHAR(100)     NULL,
    name             VARCHAR(200)     NOT NULL,
    description      TEXT             NULL,
    category_id      INT UNSIGNED     NULL,
    location_id      INT UNSIGNED     NULL,
    unit_id          INT UNSIGNED     NOT NULL,
    purchase_unit_id INT UNSIGNED     NULL,
    purchase_factor  DECIMAL(10,4)    NOT NULL DEFAULT 1.0000,
    buy_price        DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
    sell_price       DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
    min_stock        DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    is_active        TINYINT(1)       NOT NULL DEFAULT 1,
    created_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_code (code),
    KEY idx_products_category (category_id),
    KEY idx_products_location (location_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)      REFERENCES product_categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_products_location FOREIGN KEY (location_id)      REFERENCES locations(id)          ON DELETE SET NULL,
    CONSTRAINT fk_products_unit     FOREIGN KEY (unit_id)          REFERENCES quantity_units(id),
    CONSTRAINT fk_products_pu       FOREIGN KEY (purchase_unit_id) REFERENCES quantity_units(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 9: stock
-- ============================================================
CREATE TABLE IF NOT EXISTS stock (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id    INT UNSIGNED  NOT NULL,
    location_id   INT UNSIGNED  NOT NULL,
    quantity      DECIMAL(10,2) NOT NULL,
    buy_price     DECIMAL(15,2) NULL,
    batch_no      VARCHAR(50)   NULL,
    expired_date  DATE          NULL,
    received_date DATE          NOT NULL DEFAULT (CURDATE()),
    stock_ref     VARCHAR(64)   NOT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_stock_product  (product_id),
    KEY idx_stock_location (location_id),
    KEY idx_stock_ref      (stock_ref),
    CONSTRAINT fk_stock_product  FOREIGN KEY (product_id)  REFERENCES products(id),
    CONSTRAINT fk_stock_location FOREIGN KEY (location_id) REFERENCES locations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 10: stock_log
-- ============================================================
CREATE TABLE IF NOT EXISTS stock_log (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id       INT UNSIGNED  NOT NULL,
    location_id      INT UNSIGNED  NOT NULL,
    quantity         DECIMAL(10,2) NOT NULL,
    transaction_type ENUM('purchase','sales','adjustment_plus','adjustment_minus','transfer_in','transfer_out','return_in','return_out') NOT NULL,
    reference_id     INT UNSIGNED  NULL,
    reference_type   VARCHAR(30)   NULL,
    stock_ref        VARCHAR(64)   NULL,
    buy_price        DECIMAL(15,2) NULL,
    sell_price       DECIMAL(15,2) NULL,
    batch_no         VARCHAR(50)   NULL,
    expired_date     DATE          NULL,
    notes            TEXT          NULL,
    user_id          INT UNSIGNED  NOT NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_stock_log_product   (product_id),
    KEY idx_stock_log_type      (transaction_type),
    KEY idx_stock_log_ref       (reference_id, reference_type),
    KEY idx_stock_log_created   (created_at),
    CONSTRAINT fk_stock_log_product  FOREIGN KEY (product_id)  REFERENCES products(id),
    CONSTRAINT fk_stock_log_location FOREIGN KEY (location_id) REFERENCES locations(id),
    CONSTRAINT fk_stock_log_user     FOREIGN KEY (user_id)     REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 11: purchase_orders
-- ============================================================
CREATE TABLE IF NOT EXISTS purchase_orders (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    po_number     VARCHAR(30)   NOT NULL,
    supplier_id   INT UNSIGNED  NULL,
    location_id   INT UNSIGNED  NOT NULL,
    order_date    DATE          NOT NULL,
    received_date DATE          NULL,
    status        ENUM('draft','ordered','partial','received','cancelled') NOT NULL DEFAULT 'draft',
    total_amount  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes         TEXT          NULL,
    user_id       INT UNSIGNED  NOT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_po_number (po_number),
    KEY idx_po_supplier (supplier_id),
    CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    CONSTRAINT fk_po_location FOREIGN KEY (location_id) REFERENCES locations(id),
    CONSTRAINT fk_po_user     FOREIGN KEY (user_id)     REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 12: purchase_order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    po_id        INT UNSIGNED  NOT NULL,
    product_id   INT UNSIGNED  NOT NULL,
    quantity     DECIMAL(10,2) NOT NULL,
    received_qty DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    unit_price   DECIMAL(15,2) NOT NULL,
    subtotal     DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    batch_no     VARCHAR(50)   NULL,
    expired_date DATE          NULL,
    notes        TEXT          NULL,
    PRIMARY KEY (id),
    KEY idx_poi_po      (po_id),
    KEY idx_poi_product (product_id),
    CONSTRAINT fk_poi_po      FOREIGN KEY (po_id)      REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_poi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 13: sales_orders
-- ============================================================
CREATE TABLE IF NOT EXISTS sales_orders (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    so_number      VARCHAR(30)   NOT NULL,
    customer_id    INT UNSIGNED  NULL,
    customer_name  VARCHAR(150)  NULL,
    order_date     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status         ENUM('draft','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    payment_method ENUM('cash','transfer','credit') NOT NULL DEFAULT 'cash',
    subtotal       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_percent    DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    tax_amount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_amount   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes          TEXT          NULL,
    user_id        INT UNSIGNED  NOT NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_so_number (so_number),
    KEY idx_so_customer (customer_id),
    KEY idx_so_status   (status, payment_status),
    KEY idx_so_date     (order_date),
    CONSTRAINT fk_so_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_so_user     FOREIGN KEY (user_id)     REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 14: sales_order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS sales_order_items (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    so_id        INT UNSIGNED  NOT NULL,
    product_id   INT UNSIGNED  NOT NULL,
    product_name VARCHAR(200)  NOT NULL,
    quantity     DECIMAL(10,2) NOT NULL,
    unit_price   DECIMAL(15,2) NOT NULL,
    discount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    subtotal     DECIMAL(15,2) GENERATED ALWAYS AS ((quantity * unit_price) - discount) STORED,
    notes        TEXT          NULL,
    PRIMARY KEY (id),
    KEY idx_soi_so      (so_id),
    KEY idx_soi_product (product_id),
    CONSTRAINT fk_soi_so      FOREIGN KEY (so_id)      REFERENCES sales_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_soi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 15: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    so_id          INT UNSIGNED  NOT NULL,
    amount         DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash','transfer','credit') NOT NULL DEFAULT 'cash',
    payment_date   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reference_no   VARCHAR(100)  NULL,
    notes          TEXT          NULL,
    user_id        INT UNSIGNED  NOT NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payments_so   (so_id),
    KEY idx_payments_date (payment_date),
    CONSTRAINT fk_payments_so   FOREIGN KEY (so_id)    REFERENCES sales_orders(id),
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id)  REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 16: audit_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED  NULL,
    action      VARCHAR(100)  NOT NULL,
    table_name  VARCHAR(50)   NULL,
    record_id   INT UNSIGNED  NULL,
    old_values  JSON          NULL,
    new_values  JSON          NULL,
    ip_address  VARCHAR(45)   NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user    (user_id),
    KEY idx_audit_action  (action),
    KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 17: settings
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    key_name    VARCHAR(100)  NOT NULL,
    value       TEXT          NULL,
    description VARCHAR(255)  NULL,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VIEW 1: v_stock_current
-- ============================================================
CREATE OR REPLACE VIEW v_stock_current AS
SELECT
    s.product_id,
    s.location_id,
    p.code             AS product_code,
    p.name             AS product_name,
    l.name             AS location_name,
    qu.name            AS unit_name,
    SUM(s.quantity)    AS total_quantity,
    MIN(s.expired_date) AS nearest_expired,
    p.min_stock,
    p.sell_price,
    p.buy_price,
    CASE WHEN SUM(s.quantity) <= p.min_stock THEN 1 ELSE 0 END AS is_low_stock
FROM stock s
JOIN products       p  ON s.product_id  = p.id
JOIN locations      l  ON s.location_id = l.id
JOIN quantity_units qu ON p.unit_id     = qu.id
GROUP BY s.product_id, s.location_id, p.code, p.name, l.name, qu.name, p.min_stock, p.sell_price, p.buy_price;

-- ============================================================
-- VIEW 2: v_stock_total
-- ============================================================
CREATE OR REPLACE VIEW v_stock_total AS
SELECT
    p.id               AS product_id,
    p.code             AS product_code,
    p.name             AS product_name,
    qu.name            AS unit_name,
    SUM(s.quantity)    AS total_quantity,
    p.min_stock,
    p.sell_price,
    p.buy_price,
    CASE WHEN SUM(s.quantity) <= p.min_stock THEN 1 ELSE 0 END AS is_low_stock
FROM products p
LEFT JOIN stock         s  ON s.product_id = p.id
JOIN  quantity_units qu ON p.unit_id     = qu.id
GROUP BY p.id, p.code, p.name, qu.name, p.min_stock, p.sell_price, p.buy_price;

-- ============================================================
-- DATA AWAL (SEED DATA)
-- ============================================================
INSERT INTO users (username, full_name, email, password, role) VALUES
('admin', 'Administrator', 'admin@erp.local',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO quantity_units (name, name_plural) VALUES
('pcs',    'pieces'),
('kg',     'kilograms'),
('liter',  'liters'),
('box',    'boxes'),
('karton', 'kartons'),
('lusin',  'lusins'),
('meter',  'meters'),
('lembar', 'lembars');

INSERT INTO locations (name, description) VALUES
('Gudang Utama', 'Lokasi penyimpanan stok utama'),
('Toko',         'Etalase atau area penjualan langsung');

INSERT INTO product_categories (name) VALUES
('Umum'),
('Elektronik'),
('Alat Tulis Kantor'),
('Makanan & Minuman'),
('Spare Part');

INSERT INTO settings (key_name, value, description) VALUES
('app_name',       'ERP System',  'Nama aplikasi'),
('app_currency',   'Rp',          'Simbol mata uang'),
('app_tax_percent','11',          'Persentase PPN default (%)'),
('po_prefix',      'PO',          'Prefix nomor Purchase Order'),
('so_prefix',      'SO',          'Prefix nomor Sales Order'),
('so_counter',     '0',           'Counter SO terakhir'),
('po_counter',     '0',           'Counter PO terakhir');