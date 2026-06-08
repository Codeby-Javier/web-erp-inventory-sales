<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; require __DIR__ . '/../layout/sidebar.php'; ?>
<h2>Laporan Stok</h2>
<p>Total Nilai Stok: <?= Helper::e((string) $totalStockValue) ?></p>
<pre><?php print_r($rows); ?></pre>
<?php require __DIR__ . '/../layout/footer.php'; ?>