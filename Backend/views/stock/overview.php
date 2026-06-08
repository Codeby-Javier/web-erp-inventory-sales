<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$items = is_array($items ?? null) ? $items : [];
$locations = is_array($locations ?? null) ? $locations : [];
$categories = is_array($categories ?? null) ? $categories : [];
$filters = is_array($filters ?? null) ? $filters : [];
?>
<h2>Overview Stok</h2>
<p>
    <a href="<?= APP_URL ?>/stock/in">Stock In</a> |
    <a href="<?= APP_URL ?>/stock/out">Stock Out</a> |
    <a href="<?= APP_URL ?>/stock/opname">Opname</a> |
    <a href="<?= APP_URL ?>/stock/log">Log Stok</a>
</p>

<form method="get" action="<?= APP_URL ?>/stock/overview">
    <label>Cari Produk</label><br>
    <input type="text" name="search" value="<?= Helper::e((string) ($filters['search'] ?? '')) ?>"><br>
    <label>Lokasi</label><br>
    <select name="location_id">
        <option value="">Semua lokasi</option>
        <?php foreach ($locations as $location): ?>
            <?php $selected = (string) ($filters['locationId'] ?? '') === (string) ($location['id'] ?? ''); ?>
            <option value="<?= (int) ($location['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($location['name'] ?? 'Lokasi')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <label>Kategori</label><br>
    <select name="category_id">
        <option value="">Semua kategori</option>
        <?php foreach ($categories as $category): ?>
            <?php $selected = (string) ($filters['categoryId'] ?? '') === (string) ($category['id'] ?? ''); ?>
            <option value="<?= (int) ($category['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($category['name'] ?? 'Kategori')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <label>
        <input type="checkbox" name="low_stock_only" value="1"<?= (string) ($filters['lowStockOnly'] ?? '') === '1' ? ' checked' : '' ?>>
        Hanya stok menipis
    </label><br><br>
    <button type="submit">Filter</button>
    <a href="<?= APP_URL ?>/stock/overview">Reset</a>
</form>

<?php if ($items === []): ?>
<p>Tidak ada data stok.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Produk</th>
        <th>Lokasi</th>
        <th>Qty</th>
        <th>Harga Beli</th>
        <th>Nilai Stok</th>
        <th>Min Stock</th>
        <th>Status</th>
    </tr>
    <?php foreach ($items as $item): ?>
        <?php
        $qty = (float) ($item['total_quantity'] ?? 0);
        $buyPrice = (float) ($item['buy_price'] ?? 0);
        ?>
        <tr>
            <td><?= Helper::e((string) ($item['product_code'] ?? '')) ?> - <?= Helper::e((string) ($item['product_name'] ?? 'Produk')) ?></td>
            <td><?= Helper::e((string) ($item['location_name'] ?? $item['location_id'] ?? '-')) ?></td>
            <td><?= Helper::e((string) $qty) ?></td>
            <td><?= Helper::e(Helper::formatRupiah($buyPrice)) ?></td>
            <td><?= Helper::e(Helper::formatRupiah($qty * $buyPrice)) ?></td>
            <td><?= Helper::e((string) ($item['min_stock'] ?? 0)) ?></td>
            <td><?= (int) ($item['is_low_stock'] ?? 0) === 1 ? 'Menipis' : 'Aman' ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
