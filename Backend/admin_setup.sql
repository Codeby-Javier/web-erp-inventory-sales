-- ============================================================
-- ERP System - Admin User Setup Script
-- Jalankan ini jika belum ada user di database
-- Password default: password (bcrypt hash)
-- ============================================================

USE erp_db;

-- Insert admin user jika belum ada
INSERT IGNORE INTO users (username, full_name, email, password, role, is_active)
VALUES (
    'admin',
    'Administrator',
    'admin@erp.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1
);

-- Pastikan settings ada
INSERT IGNORE INTO settings (key_name, value, description) VALUES
('app_name',       'ERP System',  'Nama aplikasi'),
('app_currency',   'Rp',          'Simbol mata uang'),
('app_tax_percent','11',          'Persentase PPN default (%)'),
('po_prefix',      'PO',          'Prefix nomor Purchase Order'),
('so_prefix',      'SO',          'Prefix nomor Sales Order'),
('so_counter',     '0',           'Counter SO terakhir'),
('po_counter',     '0',           'Counter PO terakhir');

-- Verifikasi
SELECT id, username, full_name, role, is_active FROM users;
SELECT key_name, value FROM settings;
