<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';

$order = is_array($order ?? null) ? $order : [];
$items = is_array($items ?? null) ? $items : [];
$payments = is_array($payments ?? null) ? $payments : [];
$settings = is_array($settings ?? null) ? $settings : [];
$settingMap = [];

foreach ($settings as $setting) {
    $settingMap[(string) ($setting['key_name'] ?? '')] = (string) ($setting['value'] ?? '');
}

$appName = $settingMap['app_name'] ?? APP_NAME;
?>
<h2>Invoice</h2>
<p><strong><?= Helper::e($appName) ?></strong></p>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>No. SO</th><td><?= Helper::e((string) ($order['so_number'] ?? '-')) ?></td></tr>
    <tr><th>Tanggal</th><td><?= Helper::e(Helper::formatDatetime((string) ($order['order_date'] ?? ''))) ?></td></tr>
    <tr><th>Customer</th><td><?= Helper::e((string) ($order['customer_name_ref'] ?? $order['customer_name'] ?? '-')) ?></td></tr>
    <tr><th>Kasir</th><td><?= Helper::e((string) ($order['full_name'] ?? '-')) ?></td></tr>
</table>

<h3>Item</h3>
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

<h3>Ringkasan</h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Subtotal</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['subtotal'] ?? 0))) ?></td></tr>
    <tr><th>Pajak</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['tax_amount'] ?? 0))) ?></td></tr>
    <tr><th>Diskon</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['discount'] ?? 0))) ?></td></tr>
    <tr><th>Total</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['total_amount'] ?? 0))) ?></td></tr>
    <tr><th>Terbayar</th><td><?= Helper::e(Helper::formatRupiah((float) ($order['paid_amount'] ?? 0))) ?></td></tr>
    <tr><th>Sisa</th><td><?= Helper::e(Helper::formatRupiah(max(0, (float) ($order['total_amount'] ?? 0) - (float) ($order['paid_amount'] ?? 0)))) ?></td></tr>
</table>

<?php if ($payments !== []): ?>
<h3>Pembayaran</h3>
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
<?php require __DIR__ . '/../layout/footer.php'; ?>
