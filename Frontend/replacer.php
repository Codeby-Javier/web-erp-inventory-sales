<?php
$files = [
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/App.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Dashboard.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Product.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Stock.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Purchase.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Sales.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Category.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Supplier.vue',
    'e:/projek servbay/web-erp-inventory-sales-fixed/frontend/src/views/Customer.vue'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = str_replace('container-xl', 'container-fluid', $content);
        file_put_contents($file, $newContent);
        echo "Updated: $file\n";
    }
}
