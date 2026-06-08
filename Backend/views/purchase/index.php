<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$orders = is_array($orders ?? null) ? $orders : [];
?>
<h2>Purchase Order</h2>
<p><a href="<?= APP_URL ?>/purchase/create">Buat PO Baru</a></p>

<?php if ($orders === []): ?>
<p>Belum ada purchase order.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>No. PO</th>
        <th>Tanggal</th>
        <th>Supplier</th>
        <th>Lokasi</th>
        <th>Status</th>
        <th>Total</th>
        <th>User</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= Helper::e((string) ($order['po_number'] ?? '-')) ?></td>
            <td><?= Helper::e(Helper::formatDatetime((string) ($order['order_date'] ?? ''))) ?></td>
            <td><?= Helper::e((string) ($order['supplier_name'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($order['location_name'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($order['status'] ?? '-')) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($order['total_amount'] ?? 0))) ?></td>
            <td><?= Helper::e((string) ($order['user_name'] ?? '-')) ?></td>
            <td><a href="<?= APP_URL ?>/purchase/detail/<?= (int) ($order['id'] ?? 0) ?>">Detail</a></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
