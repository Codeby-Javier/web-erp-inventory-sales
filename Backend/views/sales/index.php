<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$orders = is_array($orders ?? null) ? $orders : [];
$filters = is_array($filters ?? null) ? $filters : [];
?>
<h2>Sales Order</h2>
<p><a href="<?= APP_URL ?>/sales/create">Buat SO Baru</a></p>

<form method="get" action="<?= APP_URL ?>/sales">
    <label>Cari</label><br>
    <input type="text" name="search" value="<?= Helper::e((string) ($filters['search'] ?? '')) ?>"><br>
    <label>Status Order</label><br>
    <select name="status">
        <?php $statusValue = (string) ($filters['status'] ?? ''); ?>
        <option value="">Semua</option>
        <option value="confirmed"<?= $statusValue === 'confirmed' ? ' selected' : '' ?>>Confirmed</option>
        <option value="cancelled"<?= $statusValue === 'cancelled' ? ' selected' : '' ?>>Cancelled</option>
    </select><br>
    <label>Status Pembayaran</label><br>
    <select name="payment_status">
        <?php $paymentValue = (string) ($filters['paymentStatus'] ?? ''); ?>
        <option value="">Semua</option>
        <option value="unpaid"<?= $paymentValue === 'unpaid' ? ' selected' : '' ?>>Unpaid</option>
        <option value="partial"<?= $paymentValue === 'partial' ? ' selected' : '' ?>>Partial</option>
        <option value="paid"<?= $paymentValue === 'paid' ? ' selected' : '' ?>>Paid</option>
    </select><br>
    <label>Dari Tanggal</label><br>
    <input type="date" name="date_from" value="<?= Helper::e((string) ($filters['dateFrom'] ?? '')) ?>"><br>
    <label>Sampai Tanggal</label><br>
    <input type="date" name="date_to" value="<?= Helper::e((string) ($filters['dateTo'] ?? '')) ?>"><br><br>
    <button type="submit">Filter</button>
    <a href="<?= APP_URL ?>/sales">Reset</a>
</form>

<?php if ($orders === []): ?>
<p>Belum ada sales order.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>No. SO</th>
        <th>Tanggal</th>
        <th>Customer</th>
        <th>Status</th>
        <th>Pembayaran</th>
        <th>Total</th>
        <th>User</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= Helper::e((string) ($order['so_number'] ?? '-')) ?></td>
            <td><?= Helper::e(Helper::formatDatetime((string) ($order['order_date'] ?? ''))) ?></td>
            <td><?= Helper::e((string) ($order['customer_name_ref'] ?? $order['customer_name'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($order['status'] ?? '-')) ?></td>
            <td><?= Helper::e((string) ($order['payment_status'] ?? '-')) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($order['total_amount'] ?? 0))) ?></td>
            <td><?= Helper::e((string) ($order['full_name'] ?? '-')) ?></td>
            <td>
                <a href="<?= APP_URL ?>/sales/detail/<?= (int) ($order['id'] ?? 0) ?>">Detail</a> |
                <a href="<?= APP_URL ?>/sales/print/<?= (int) ($order['id'] ?? 0) ?>" target="_blank" rel="noopener noreferrer">Print</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
