<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; require __DIR__ . '/../layout/sidebar.php'; ?>
<h2>Laporan Penjualan</h2>
<p>Total: <?= Helper::e((string) $totalSales) ?> | Item: <?= (int) $totalItems ?> | Rata-rata: <?= Helper::e((string) $averagePerTransaction) ?></p>
<pre><?php print_r($rows); ?></pre>
<?php require __DIR__ . '/../layout/footer.php'; ?>