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

// Kategori
$catElektronik = $db->insert('product_categories', ['name' => 'Elektronik', 'description' => 'Barang elektronik dan gadget']);
$catPakaian = $db->insert('product_categories', ['name' => 'Pakaian', 'description' => 'Baju, celana, jaket']);
$catMakanan = $db->insert('product_categories', ['name' => 'Makanan', 'description' => 'Makanan ringan dan bahan pokok']);
$catMinuman = $db->insert('product_categories', ['name' => 'Minuman', 'description' => 'Minuman kemasan']);
$catAtk = $db->insert('product_categories', ['name' => 'Alat Tulis Kantor', 'description' => 'Perlengkapan ATK']);

// Satuan
$unitPcs = $db->insert('quantity_units', ['name' => 'Pcs', 'description' => 'Pieces']);
$unitBox = $db->insert('quantity_units', ['name' => 'Box', 'description' => 'Kotak / Dus']);
$unitKg = $db->insert('quantity_units', ['name' => 'Kg', 'description' => 'Kilogram']);
$unitLusin = $db->insert('quantity_units', ['name' => 'Lusin', 'description' => '12 Pieces']);

// Lokasi Gudang
$locUtama = $db->insert('locations', ['name' => 'Gudang Utama', 'description' => 'Jakarta Pusat']);
$locCabang = $db->insert('locations', ['name' => 'Gudang Cabang Bandung', 'description' => 'Bandung']);

// Supplier
$sup1 = $db->insert('suppliers', ['name' => 'PT. Teknologi Global', 'contact_name' => 'Budi', 'phone' => '0812345678', 'address' => 'Jl. Sudirman 1']);
$sup2 = $db->insert('suppliers', ['name' => 'CV. Sandang Sejahtera', 'contact_name' => 'Sari', 'phone' => '0822123456', 'address' => 'Pusat Grosir Tanah Abang']);
$sup3 = $db->insert('suppliers', ['name' => 'PD. Pangan Makmur', 'contact_name' => 'Joko', 'phone' => '0833987654', 'address' => 'Pasar Induk Kramat Jati']);
$sup4 = $db->insert('suppliers', ['name' => 'Toko Alat Tulis Kita', 'contact_name' => 'Rina', 'phone' => '0844777888', 'address' => 'Jl. Pramuka']);

// Customer
$cus1 = $db->insert('customers', ['name' => 'Toko Maju Jaya', 'phone' => '0898765432', 'email' => 'contact@maju.com', 'address' => 'Jl. Merdeka 10']);
$cus2 = $db->insert('customers', ['name' => 'Warung Budi', 'phone' => '0811223344', 'email' => 'budi@warung.com', 'address' => 'Jl. Kenari']);
$cus3 = $db->insert('customers', ['name' => 'Koperasi Karyawan', 'phone' => '0822334455', 'email' => 'kopkar@pt.com', 'address' => 'Kawasan Industri']);

// Produk
$products = [
    // Elektronik
    ['code' => 'PRD-EL-001', 'name' => 'Laptop ASUS ROG', 'category_id' => $catElektronik, 'unit_id' => $unitPcs, 'buy_price' => 15000000, 'sell_price' => 18000000, 'is_active' => 1],
    ['code' => 'PRD-EL-002', 'name' => 'Mouse Logitech G502', 'category_id' => $catElektronik, 'unit_id' => $unitPcs, 'buy_price' => 800000, 'sell_price' => 1000000, 'is_active' => 1],
    ['code' => 'PRD-EL-003', 'name' => 'Keyboard Mechanical RK61', 'category_id' => $catElektronik, 'unit_id' => $unitPcs, 'buy_price' => 600000, 'sell_price' => 750000, 'is_active' => 1],
    // Pakaian
    ['code' => 'PRD-PK-001', 'name' => 'Jaket Eiger', 'category_id' => $catPakaian, 'unit_id' => $unitPcs, 'buy_price' => 400000, 'sell_price' => 550000, 'is_active' => 1],
    ['code' => 'PRD-PK-002', 'name' => 'Kemeja Polos Lengan Panjang', 'category_id' => $catPakaian, 'unit_id' => $unitLusin, 'buy_price' => 600000, 'sell_price' => 800000, 'is_active' => 1],
    ['code' => 'PRD-PK-003', 'name' => 'Celana Jeans Denim', 'category_id' => $catPakaian, 'unit_id' => $unitPcs, 'buy_price' => 150000, 'sell_price' => 250000, 'is_active' => 1],
    // Makanan
    ['code' => 'PRD-MK-001', 'name' => 'Indomie Goreng (Karton)', 'category_id' => $catMakanan, 'unit_id' => $unitBox, 'buy_price' => 105000, 'sell_price' => 120000, 'is_active' => 1],
    ['code' => 'PRD-MK-002', 'name' => 'Beras Pandan Wangi 5kg', 'category_id' => $catMakanan, 'unit_id' => $unitPcs, 'buy_price' => 75000, 'sell_price' => 85000, 'is_active' => 1],
    ['code' => 'PRD-MK-003', 'name' => 'Gula Pasir 1kg', 'category_id' => $catMakanan, 'unit_id' => $unitKg, 'buy_price' => 15000, 'sell_price' => 17000, 'is_active' => 1],
    // Minuman
    ['code' => 'PRD-MN-001', 'name' => 'Aqua Botol 600ml (Karton)', 'category_id' => $catMinuman, 'unit_id' => $unitBox, 'buy_price' => 45000, 'sell_price' => 55000, 'is_active' => 1],
    ['code' => 'PRD-MN-002', 'name' => 'Teh Botol Sosro (Karton)', 'category_id' => $catMinuman, 'unit_id' => $unitBox, 'buy_price' => 60000, 'sell_price' => 75000, 'is_active' => 1],
    // ATK
    ['code' => 'PRD-AT-001', 'name' => 'Kertas HVS A4 80gr', 'category_id' => $catAtk, 'unit_id' => $unitBox, 'buy_price' => 45000, 'sell_price' => 55000, 'is_active' => 1],
    ['code' => 'PRD-AT-002', 'name' => 'Pulpen Faster (Pack)', 'category_id' => $catAtk, 'unit_id' => $unitBox, 'buy_price' => 30000, 'sell_price' => 40000, 'is_active' => 1],
];

$prodIds = [];
foreach ($products as $p) {
    $prodIds[$p['code']] = $db->insert('products', $p);
}

// Purchase Orders (Masuk Stok)
$po1 = $db->insert('purchase_orders', ['po_number' => 'PO-2026-001', 'supplier_id' => $sup1, 'user_id' => 1, 'location_id' => $locUtama, 'order_date' => date('Y-m-d', strtotime('-10 days')), 'total_amount' => 50000000, 'status' => 'received']);
$db->insert('purchase_order_items', ['po_id' => $po1, 'product_id' => $prodIds['PRD-EL-001'], 'quantity' => 10, 'unit_price' => 15000000]);
$db->insert('purchase_order_items', ['po_id' => $po1, 'product_id' => $prodIds['PRD-EL-002'], 'quantity' => 20, 'unit_price' => 800000]);

$po2 = $db->insert('purchase_orders', ['po_number' => 'PO-2026-002', 'supplier_id' => $sup2, 'user_id' => 1, 'location_id' => $locUtama, 'order_date' => date('Y-m-d', strtotime('-9 days')), 'total_amount' => 10000000, 'status' => 'received']);
$db->insert('purchase_order_items', ['po_id' => $po2, 'product_id' => $prodIds['PRD-PK-001'], 'quantity' => 15, 'unit_price' => 400000]);
$db->insert('purchase_order_items', ['po_id' => $po2, 'product_id' => $prodIds['PRD-PK-003'], 'quantity' => 30, 'unit_price' => 150000]);

$po3 = $db->insert('purchase_orders', ['po_number' => 'PO-2026-003', 'supplier_id' => $sup3, 'user_id' => 1, 'location_id' => $locCabang, 'order_date' => date('Y-m-d', strtotime('-8 days')), 'total_amount' => 15000000, 'status' => 'received']);
$db->insert('purchase_order_items', ['po_id' => $po3, 'product_id' => $prodIds['PRD-MK-001'], 'quantity' => 50, 'unit_price' => 105000]);
$db->insert('purchase_order_items', ['po_id' => $po3, 'product_id' => $prodIds['PRD-MN-001'], 'quantity' => 100, 'unit_price' => 45000]);

$po4 = $db->insert('purchase_orders', ['po_number' => 'PO-2026-004', 'supplier_id' => $sup4, 'user_id' => 1, 'location_id' => $locUtama, 'order_date' => date('Y-m-d', strtotime('-7 days')), 'total_amount' => 5000000, 'status' => 'received']);
$db->insert('purchase_order_items', ['po_id' => $po4, 'product_id' => $prodIds['PRD-AT-001'], 'quantity' => 20, 'unit_price' => 45000]);
$db->insert('purchase_order_items', ['po_id' => $po4, 'product_id' => $prodIds['PRD-AT-002'], 'quantity' => 50, 'unit_price' => 30000]);

// Sales Orders (Keluar Stok)
$so1 = $db->insert('sales_orders', ['so_number' => 'SO-2026-001', 'customer_id' => $cus1, 'user_id' => 1, 'order_date' => date('Y-m-d', strtotime('-5 days')), 'total_amount' => 18000000, 'payment_method' => 'transfer', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so1, 'product_id' => $prodIds['PRD-EL-001'], 'product_name' => 'Laptop ASUS ROG', 'quantity' => 1, 'unit_price' => 18000000]);

$so2 = $db->insert('sales_orders', ['so_number' => 'SO-2026-002', 'customer_id' => $cus2, 'user_id' => 1, 'order_date' => date('Y-m-d', strtotime('-4 days')), 'total_amount' => 1200000, 'payment_method' => 'cash', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so2, 'product_id' => $prodIds['PRD-MK-001'], 'product_name' => 'Indomie Goreng (Karton)', 'quantity' => 10, 'unit_price' => 120000]);

$so3 = $db->insert('sales_orders', ['so_number' => 'SO-2026-003', 'customer_name' => 'Walk-in Customer', 'user_id' => 1, 'order_date' => date('Y-m-d', strtotime('-2 days')), 'total_amount' => 250000, 'payment_method' => 'cash', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so3, 'product_id' => $prodIds['PRD-PK-003'], 'product_name' => 'Celana Jeans Denim', 'quantity' => 1, 'unit_price' => 250000]);

$so4 = $db->insert('sales_orders', ['so_number' => 'SO-2026-004', 'customer_id' => $cus3, 'user_id' => 1, 'order_date' => date('Y-m-d', strtotime('-1 days')), 'total_amount' => 550000, 'payment_method' => 'transfer', 'status' => 'confirmed', 'payment_status' => 'paid']);
$db->insert('sales_order_items', ['so_id' => $so4, 'product_id' => $prodIds['PRD-AT-001'], 'product_name' => 'Kertas HVS A4 80gr', 'quantity' => 10, 'unit_price' => 55000]);

echo "Seeder completed successfully with comprehensive dummy data!\n";
