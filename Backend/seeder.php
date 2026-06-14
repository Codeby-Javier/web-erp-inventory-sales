<?php

const BASE_PATH = __DIR__;
require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Helper.php';
require_once BASE_PATH . '/core/Database.php';
$db = Database::getInstance();

echo "Running seeder...\n";

$db->query('SET FOREIGN_KEY_CHECKS = 0');
$db->query('TRUNCATE TABLE sales_order_items');
$db->query('TRUNCATE TABLE sales_orders');
$db->query('TRUNCATE TABLE purchase_order_items');
$db->query('TRUNCATE TABLE purchase_orders');
$db->query('TRUNCATE TABLE products');
$db->query('TRUNCATE TABLE product_categories');
$db->query('TRUNCATE TABLE quantity_units');
$db->query('TRUNCATE TABLE locations');
$db->query('TRUNCATE TABLE suppliers');
$db->query('TRUNCATE TABLE customers');
$db->query('SET FOREIGN_KEY_CHECKS = 1');

$catElektronik = $db->insert('product_categories', ['name' => 'Elektronik', 'description' => 'Barang elektronik dan gadget']);
$catPakaian = $db->insert('product_categories', ['name' => 'Pakaian', 'description' => 'Baju, celana, jaket']);

$unitPcs = $db->insert('quantity_units', ['name' => 'Pcs', 'description' => 'Pieces']);
$unitBox = $db->insert('quantity_units', ['name' => 'Box', 'description' => 'Kotak']);

$locUtama = $db->insert('locations', ['name' => 'Gudang Utama', 'description' => 'Jakarta Pusat']);

$sup1 = $db->insert('suppliers', ['name' => 'PT. Teknologi Global', 'contact_name' => 'Budi', 'phone' => '0812345678']);
$cus1 = $db->insert('customers', ['name' => 'Toko Maju Jaya', 'phone' => '0898765432', 'email' => 'contact@maju.com']);

$prod1 = $db->insert('products', ['code' => 'PRD-001', 'name' => 'Laptop ASUS ROG', 'category_id' => $catElektronik, 'unit_id' => $unitPcs, 'buy_price' => 15000000, 'sell_price' => 18000000, 'is_active' => 1]);
$prod2 = $db->insert('products', ['code' => 'PRD-002', 'name' => 'Mouse Logitech G502', 'category_id' => $catElektronik, 'unit_id' => $unitPcs, 'buy_price' => 800000, 'sell_price' => 1000000, 'is_active' => 1]);
$prod3 = $db->insert('products', ['code' => 'PRD-003', 'name' => 'Jaket Eiger', 'category_id' => $catPakaian, 'unit_id' => $unitPcs, 'buy_price' => 400000, 'sell_price' => 550000, 'is_active' => 1]);

$po1 = $db->insert('purchase_orders', ['po_number' => 'PO-2026-001', 'supplier_id' => $sup1, 'user_id' => 1, 'location_id' => $locUtama, 'order_date' => date('Y-m-d'), 'total_amount' => 55000000, 'status' => 'received']);
$db->insert('purchase_order_items', ['po_id' => $po1, 'product_id' => $prod1, 'quantity' => 3, 'unit_price' => 15000000]);
$db->insert('purchase_order_items', ['po_id' => $po1, 'product_id' => $prod2, 'quantity' => 10, 'unit_price' => 800000]);
$db->insert('purchase_order_items', ['po_id' => $po1, 'product_id' => $prod3, 'quantity' => 5, 'unit_price' => 400000]);

$so1 = $db->insert('sales_orders', ['so_number' => 'SO-2026-001', 'customer_id' => $cus1, 'user_id' => 1, 'order_date' => date('Y-m-d'), 'total_amount' => 19000000, 'payment_method' => 'transfer', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so1, 'product_id' => $prod1, 'product_name' => 'Laptop ASUS ROG', 'quantity' => 1, 'unit_price' => 18000000]);
$db->insert('sales_order_items', ['so_id' => $so1, 'product_id' => $prod2, 'product_name' => 'Mouse Logitech G502', 'quantity' => 1, 'unit_price' => 1000000]);

$so2 = $db->insert('sales_orders', ['so_number' => 'SO-2026-002', 'customer_name' => 'Walk-in Customer', 'user_id' => 1, 'order_date' => date('Y-m-d'), 'total_amount' => 550000, 'payment_method' => 'cash', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so2, 'product_id' => $prod3, 'product_name' => 'Jaket Eiger', 'quantity' => 1, 'unit_price' => 550000]);

$prod4 = $db->insert('products', ['code' => 'PRD-004', 'name' => 'Kabel HDMI', 'category_id' => $catElektronik, 'unit_id' => $unitPcs, 'buy_price' => 50000, 'sell_price' => 75000, 'is_active' => 1]);
$po2 = $db->insert('purchase_orders', ['po_number' => 'PO-2026-002', 'supplier_id' => $sup1, 'user_id' => 1, 'location_id' => $locUtama, 'order_date' => date('Y-m-d'), 'total_amount' => 250000, 'status' => 'received']);
$db->insert('purchase_order_items', ['po_id' => $po2, 'product_id' => $prod4, 'quantity' => 5, 'unit_price' => 50000]);
$so3 = $db->insert('sales_orders', ['so_number' => 'SO-2026-003', 'customer_name' => 'Guest', 'user_id' => 1, 'order_date' => date('Y-m-d'), 'total_amount' => 300000, 'payment_method' => 'cash', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so3, 'product_id' => $prod4, 'product_name' => 'Kabel HDMI', 'quantity' => 4, 'unit_price' => 75000]);

// Re-calculate the views by triggering some stored proc or just let the views query it.
// The views `v_stock_total` dynamically query the tables, so we are good.

echo "Seeder completed successfully!\n";
