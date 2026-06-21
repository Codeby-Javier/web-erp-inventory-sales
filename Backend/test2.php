<?php
const BASE_PATH = __DIR__;
require 'config/env.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();
$sql = "SELECT p.*, pc.name AS category_name, qu.name AS unit_name, COALESCE(st.total_quantity, 0) AS stock_quantity
        FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        LEFT JOIN quantity_units qu ON p.unit_id = qu.id
        LEFT JOIN v_stock_total st ON p.id = st.product_id
        WHERE 1=1 ORDER BY p.name ASC LIMIT 2";
print_r($db->fetchAll($sql));
