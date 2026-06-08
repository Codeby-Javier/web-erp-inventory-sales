<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$items = is_array($items ?? null) ? $items : [];
$csrfField = (string) ($csrfField ?? '');
?>
<h2>Stock Opname</h2>
<p><a href="<?= APP_URL ?>/stock/overview">Kembali ke overview stok</a></p>

<?php if ($items === []): ?>
<p>Tidak ada data stok untuk opname.</p>
<?php else: ?>
<form method="post" action="<?= APP_URL ?>/stock/process-opname">
    <?= $csrfField ?>
    <label>Catatan</label><br>
    <textarea name="notes" rows="3" cols="60"></textarea><br><br>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Produk</th>
            <th>Lokasi</th>
            <th>Qty Sistem</th>
            <th>Qty Aktual</th>
        </tr>
        <?php foreach ($items as $index => $item): ?>
            <?php $productCode = (string) ($item['product_code'] ?? ''); ?>
            <tr>
                <td>
                    <?= Helper::e($productCode) ?>
                    <?= $productCode !== '' ? ' - ' : '' ?>
                    <?= Helper::e((string) ($item['product_name'] ?? 'Produk')) ?>
                    <input type="hidden" name="items[<?= (int) $index ?>][product_id]" value="<?= (int) ($item['product_id'] ?? 0) ?>">
                    <input type="hidden" name="items[<?= (int) $index ?>][location_id]" value="<?= (int) ($item['location_id'] ?? 0) ?>">
                </td>
                <td><?= Helper::e((string) ($item['location_name'] ?? $item['location_id'] ?? '-')) ?></td>
                <td><?= Helper::e((string) ($item['total_quantity'] ?? 0)) ?></td>
                <td><input type="text" name="items[<?= (int) $index ?>][actual_quantity]" value="<?= Helper::e((string) ($item['total_quantity'] ?? 0)) ?>"></td>
            </tr>
        <?php endforeach; ?>
    </table><br>

    <button type="submit">Proses Opname</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
