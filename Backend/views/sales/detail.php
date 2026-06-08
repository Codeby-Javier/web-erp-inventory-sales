<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$order = is_array($order ?? null) ? $order : [];
$items = is_array($items ?? null) ? $items : [];
$payments = is_array($payments ?? null) ? $payments : [];
$status = (string) ($order['status'] ?? '');
$paymentStatus = (string) ($order['payment_status'] ?? '');
$remaining = max(0, (float) ($order['total_amount'] ?? 0) - (float) ($order['paid_amount'] ?? 0));
$canPay = $status !== 'cancelled' && $remaining > 0;
$canCancel = $status !== 'cancelled';
$csrfField = (string) ($csrfField ?? '');
?>
<h2>Detail Sales Order</h2>
<p>
    <a href="<?= APP_URL ?>/sales">Kembali ke daftar SO</a> |
    <a href="<?= APP_URL ?>/sales/print/<?= (int) ($order['id'] ?? 0) ?>" target="_blank" rel="noopener noreferrer">Cetak Invoice</a>
</p>

<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>No. SO</th><td><?= Helper::e((string) ($order['so_number'] ?? '-')) ?></td></tr>
    <tr><th>Tanggal</th><td><?= Helper::e(Helper::formatDatetime((string) ($order['order_date'] ?? ''))) ?></td></tr>
    <tr><th>Customer</th><td><?= Helper::e((string) ($order['customer_name_ref'] ?? $order['customer_name'] ?? '-')) ?></td></tr>
    <tr><th>Status Order</th><td><?= Helper::e($status !== '' ? $status : '-') ?></td></tr>
    <tr><th>Status Pembayaran</th><td><?= Helper::e($paymentStatus !== '' ? $paymentStatus : '-') ?></td></tr>
    <tr><th>Metode Bayar</th><td><?= Helper::e((string) ($order['payment_method'] ?? '-')) ?></td></tr>
    <tr><th>Subtotal</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['subtotal'] ?? 0))) ?></td></tr>
    <tr><th>Pajak</th><td><?= Helper::e((string) ($order['tax_percent'] ?? 0)) ?>%</td></tr>
    <tr><th>Diskon</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['discount'] ?? 0))) ?></td></tr>
    <tr><th>Total</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['total_amount'] ?? 0))) ?></td></tr>
    <tr><th>Sudah Dibayar</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['paid_amount'] ?? 0))) ?></td></tr>
    <tr><th>Sisa</th><td><?= Helper::e(Helper::formatRupiah($remaining)) ?></td></tr>
    <tr><th>Kasir</th><td><?= Helper::e((string) ($order['full_name'] ?? '-')) ?></td></tr>
    <tr><th>Catatan</th><td><?= nl2br(Helper::e((string) ($order['notes'] ?? '-'))) ?></td></tr>
</table>

<h3>Item Penjualan</h3>
<?php if ($items === []): ?>
<p>Belum ada item.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Produk</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Diskon</th>
        <th>Total</th>
    </tr>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= Helper::e((string) ($item['code'] ?? '')) ?> - <?= Helper::e((string) ($item['name'] ?? 'Produk')) ?></td>
            <td><?= Helper::e((string) ($item['quantity'] ?? 0)) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($item['unit_price'] ?? 0))) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($item['discount'] ?? 0))) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($item['total_price'] ?? 0))) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h3>Riwayat Pembayaran</h3>
<?php if ($payments === []): ?>
<p>Belum ada pembayaran.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Tanggal</th>
        <th>Metode</th>
        <th>Nominal</th>
        <th>Catatan</th>
    </tr>
    <?php foreach ($payments as $payment): ?>
        <tr>
            <td><?= Helper::e(Helper::formatDatetime((string) ($payment['payment_date'] ?? ''))) ?></td>
            <td><?= Helper::e((string) ($payment['payment_method'] ?? '-')) ?></td>
            <td><?= Helper::e(Helper::formatRupiah((float) ($payment['amount'] ?? 0))) ?></td>
            <td><?= Helper::e((string) ($payment['notes'] ?? '-')) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h3>Tambah Pembayaran</h3>
<?php if (!$canPay): ?>
<p>Pembayaran tambahan tidak tersedia untuk order ini.</p>
<?php else: ?>
<form method="post" action="<?= APP_URL ?>/sales/pay/<?= (int) ($order['id'] ?? 0) ?>">
    <?= $csrfField ?>
    <p>Sisa pembayaran: <?= Helper::e(Helper::formatRupiah($remaining)) ?></p>
    <label>Nominal</label><br>
    <input type="text" name="amount" value="<?= Helper::e((string) $remaining) ?>"><br>
    <label>Metode Pembayaran</label><br>
    <select name="payment_method">
        <option value="cash">Cash</option>
        <option value="transfer">Transfer</option>
        <option value="credit">Credit</option>
    </select><br>
    <label>Catatan</label><br>
    <input type="text" name="notes" value=""><br><br>
    <button type="submit">Simpan Pembayaran</button>
</form>
<?php endif; ?>

<h3>Batalkan Sales Order</h3>
<?php if (!$canCancel): ?>
<p>Sales order sudah dibatalkan.</p>
<?php else: ?>
<form method="post" action="<?= APP_URL ?>/sales/cancel/<?= (int) ($order['id'] ?? 0) ?>">
    <?= $csrfField ?>
    <button type="submit">Batalkan SO</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
