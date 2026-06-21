<?php
const BASE_PATH = __DIR__;
require 'config/env.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();

echo "Sales:\n";
print_r($db->fetchAll('SELECT so.*, COALESCE(c.name, so.customer_name, "-") AS customer_label, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id ORDER BY so.order_date DESC LIMIT 5'));

echo "Purchases:\n";
print_r($db->fetchAll('SELECT po.*, COALESCE(s.name, "-") AS supplier_name, l.name AS location_name, u.full_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id JOIN locations l ON po.location_id = l.id JOIN users u ON po.user_id = u.id ORDER BY po.order_date DESC LIMIT 5'));

echo "Master:\n";
print_r($db->fetchAll('SELECT * FROM product_categories LIMIT 5'));
