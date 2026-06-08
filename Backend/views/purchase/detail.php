<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$order = is_array($order ?? null) ? $order : [];
$items = is_array($items ?? null) ? $items : [];
$status = (string) ($order['status'] ?? '');
$receiveDisabled = in_array($status, ['received', 'cancelled'], true);
?>
<h2>Detail Purchase Order</h2>
<p><a href="<?= APP_URL ?>/purchase">Kembali ke daftar PO</a></p>

<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>No. PO</th><td><?= Helper::e((string) ($order['po_number'] ?? '-')) ?></td></tr>
    <tr><th>Supplier</th><td><?= Helper::e((string) ($order['supplier_name'] ?? '-')) ?></td></tr>
    <tr><th>Lokasi</th><td><?= Helper::e((string) ($order['location_name'] ?? $order['location_id'] ?? '-')) ?></td></tr>
    <tr><th>Tanggal</th><td><?= Helper::e(Helper::formatDatetime((string) ($order['order_date'] ?? ''))) ?></td></tr>
    <tr><th>Status</th><td><?= Helper::e($status !== '' ? $status : '-') ?></td></tr>
    <tr><th>Total</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['total_amount'] ?? 0))) ?></td></tr>
    <tr><th>User</th><td><?= Helper::e((string) ($order['user_name'] ?? '-')) ?></td></tr>
    <tr><th>Catatan</th><td><?= nl2br(Helper::e((string) ($order['notes'] ?? '-'))) ?></td></tr>
</table>

<h3>Item Purchase</h3>
<?php if ($items === []): ?>
<p>Belum ada item.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Produk</th>
        <th>Qty Order</th>
        <th>Qty Diterima</th>
        <th>Sisa</th>
        <th>Harga</th>
        <th>Batch</th>
        <th>Expired</th>
    </tr>
    <?php foreach ($items as $item): ?>
        <?php $remaining = max(0, (float) ($item['quantity'] ?? 0) - (float) ($item['received_qty'] ?? 0)); ?>
        <tr>
            <td><?= Helper::e((string) ($item['code'] ?? '')) ?> - <?= Helper::e((string) ($item['name'] ?? 'Produk')) ?></td>
            <td><?= Helper::e((string) ($item['quantity'] ?? 0)) ?></td>
            <td><?= Helper::e((string) ($item['received_qty'] ?? 0)) ?></td>
            <td><?= Helper::e((string) $remaining) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($item['unit_price'] ?? 0))) ?></td>
            <td><?= Helper::e((string) ($item['batch_no'] ?? '-')) ?></td>
            <td><?= Helper::e((string) (($item['expired_date'] ?? '') !== '' ? Helper::formatDate((string) $item['expired_date']) : '-')) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h3>Terima Barang</h3>
<?php if ($receiveDisabled): ?>
<p>Penerimaan tidak tersedia karena status PO adalah `<?= Helper::e($status) ?>`.</p>
<?php else: ?>
<form method="post" action="<?= APP_URL ?>/purchase/receive/<?= (int) ($order['id'] ?? 0) ?>">
    <?= $csrfField ?>
    <?php foreach ($items as $index => $item): ?>
        <?php $remaining = max(0, (float) ($item['quantity'] ?? 0) - (float) ($item['received_qty'] ?? 0)); ?>
        <?php if ($remaining <= 0): ?>
            <?php continue; ?>
        <?php endif; ?>
        <fieldset style="margin-bottom:12px;">
            <legend><?= Helper::e((string) ($item['code'] ?? '')) ?> - <?= Helper::e((string) ($item['name'] ?? 'Item')) ?></legend>
            <input type="hidden" name="items[<?= (int) $index ?>][item_id]" value="<?= (int) ($item['id'] ?? 0) ?>">
            <p>Sisa qty: <?= Helper::e((string) $remaining) ?></p>
            <label>Qty Diterima</label><br>
            <input type="text" name="items[<?= (int) $index ?>][received_qty]" value=""><br>
            <label>Batch No</label><br>
            <input type="text" name="items[<?= (int) $index ?>][batch_no]" value="<?= Helper::e((string) ($item['batch_no'] ?? '')) ?>"><br>
            <label>Expired Date</label><br>
            <input type="date" name="items[<?= (int) $index ?>][expired_date]" value="<?= Helper::e((string) ($item['expired_date'] ?? '')) ?>"><br>
        </fieldset>
    <?php endforeach; ?>
    <button type="submit">Proses Penerimaan</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
