<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$logs = is_array($logs ?? null) ? $logs : [];
$products = is_array($products ?? null) ? $products : [];
$filters = is_array($filters ?? null) ? $filters : [];
?>
<h2>Log Stok</h2>
<p><a href="<?= APP_URL ?>/stock/overview">Kembali ke overview stok</a></p>

<form method="get" action="<?= APP_URL ?>/stock/log">
    <label>Produk</label><br>
    <select name="product_id">
        <option value="">Semua produk</option>
        <?php foreach ($products as $product): ?>
            <?php $selected = (string) ($filters['productId'] ?? '') === (string) ($product['id'] ?? ''); ?>
            <option value="<?= (int) ($product['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($product['code'] ?? '')) ?> - <?= Helper::e((string) ($product['name'] ?? 'Produk')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <label>Tipe Transaksi</label><br>
    <?php $transactionType = (string) ($filters['transactionType'] ?? ''); ?>
    <select name="transaction_type">
        <option value="">Semua</option>
        <option value="purchase"<?= $transactionType === 'purchase' ? ' selected' : '' ?>>Purchase</option>
        <option value="sales"<?= $transactionType === 'sales' ? ' selected' : '' ?>>Sales</option>
        <option value="adjustment_plus"<?= $transactionType === 'adjustment_plus' ? ' selected' : '' ?>>Adjustment Plus</option>
        <option value="adjustment_minus"<?= $transactionType === 'adjustment_minus' ? ' selected' : '' ?>>Adjustment Minus</option>
        <option value="return_in"<?= $transactionType === 'return_in' ? ' selected' : '' ?>>Return In</option>
    </select><br>
    <label>Dari Tanggal</label><br>
    <input type="date" name="date_from" value="<?= Helper::e((string) ($filters['dateFrom'] ?? '')) ?>"><br>
    <label>Sampai Tanggal</label><br>
    <input type="date" name="date_to" value="<?= Helper::e((string) ($filters['dateTo'] ?? '')) ?>"><br><br>
    <button type="submit">Filter</button>
    <a href="<?= APP_URL ?>/stock/log">Reset</a>
</form>

<?php if ($logs === []): ?>
<p>Belum ada log stok.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Tanggal</th>
        <th>Produk</th>
        <th>Lokasi</th>
        <th>Tipe</th>
        <th>Qty</th>
        <th>Buy</th>
        <th>Sell</th>
        <th>User</th>
        <th>Catatan</th>
    </tr>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= Helper::e(Helper::formatDatetime((string) ($log['created_at'] ?? ''))) ?></td>
            <td><?= Helper::e((string) ($log['product_code'] ?? '')) ?> - <?= Helper::e((string) ($log['product_name'] ?? 'Produk')) ?></td>
            <td><?= Helper::e((string) ($log['location_name'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($log['transaction_type'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($log['quantity'] ?? 0)) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($log['buy_price'] ?? 0))) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($log['sell_price'] ?? 0))) ?></td>
            <td><?= Helper::e((string) ($log['user_name'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($log['notes'] ?? '-')) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
