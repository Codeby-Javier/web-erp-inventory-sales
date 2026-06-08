<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$customers = is_array($customers ?? null) ? $customers : [];
$products = is_array($products ?? null) ? $products : [];
$oldItems = is_array($old['items'] ?? null) ? $old['items'] : [];
$csrfField = (string) ($csrfField ?? '');

if ($oldItems === []) {
    $oldItems = array_fill(0, 5, []);
}

$oldItems = array_map(static fn (mixed $row): array => is_array($row) ? $row : [], $oldItems);

$paymentMethod = (string) ($old['payment_method'] ?? 'cash');
?>
<h2>Form Sales Order</h2>
<p><a href="<?= APP_URL ?>/sales">Kembali ke daftar SO</a></p>

<?php if ($errors !== []): ?>
<p>Terdapat data yang belum valid. Periksa field yang ditandai di bawah ini.</p>
<?php endif; ?>

<form method="post" action="<?= APP_URL ?>/sales/store">
    <?= $csrfField ?>

    <label>Customer</label><br>
    <select name="customer_id">
        <option value="">Walk in / tanpa customer master</option>
        <?php foreach ($customers as $customer): ?>
            <?php $selected = (string) ($old['customer_id'] ?? '') === (string) ($customer['id'] ?? ''); ?>
            <option value="<?= (int) ($customer['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($customer['name'] ?? 'Customer')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <?php if (!empty($errors['customer_id'])): ?><small><?= Helper::e((string) $errors['customer_id']) ?></small><br><?php endif; ?>
    <?php if (!empty($errors['customer'])): ?><small><?= Helper::e((string) $errors['customer']) ?></small><br><?php endif; ?>

    <label>Nama Customer Walk In</label><br>
    <input type="text" name="customer_name" value="<?= Helper::e((string) ($old['customer_name'] ?? '')) ?>"><br>

    <label>Metode Pembayaran</label><br>
    <select name="payment_method">
        <option value="cash"<?= $paymentMethod === 'cash' ? ' selected' : '' ?>>Cash</option>
        <option value="transfer"<?= $paymentMethod === 'transfer' ? ' selected' : '' ?>>Transfer</option>
        <option value="credit"<?= $paymentMethod === 'credit' ? ' selected' : '' ?>>Credit</option>
    </select><br>
    <?php if (!empty($errors['payment_method'])): ?><small><?= Helper::e((string) $errors['payment_method']) ?></small><br><?php endif; ?>

    <label>Pajak (%)</label><br>
    <input type="text" name="tax_percent" value="<?= Helper::e((string) ($old['tax_percent'] ?? '0')) ?>"><br>
    <?php if (!empty($errors['tax_percent'])): ?><small><?= Helper::e((string) $errors['tax_percent']) ?></small><br><?php endif; ?>

    <label>Diskon Order</label><br>
    <input type="text" name="discount" value="<?= Helper::e((string) ($old['discount'] ?? '0')) ?>"><br>
    <?php if (!empty($errors['discount'])): ?><small><?= Helper::e((string) $errors['discount']) ?></small><br><?php endif; ?>

    <label>Pembayaran Awal</label><br>
    <input type="text" name="paid_amount" value="<?= Helper::e((string) ($old['paid_amount'] ?? '0')) ?>"><br>
    <?php if (!empty($errors['paid_amount'])): ?><small><?= Helper::e((string) $errors['paid_amount']) ?></small><br><?php endif; ?>

    <label>Catatan</label><br>
    <textarea name="notes" rows="3" cols="60"><?= Helper::e((string) ($old['notes'] ?? '')) ?></textarea><br><br>

    <h3>Item Penjualan</h3>
    <?php if (!empty($errors['items'])): ?><small><?= Helper::e((string) $errors['items']) ?></small><br><br><?php endif; ?>

    <?php foreach ($oldItems as $i => $row): ?>
        <fieldset style="margin-bottom:12px;">
            <legend>Item <?= (int) $i + 1 ?></legend>

            <label>Produk</label><br>
            <select name="items[<?= (int) $i ?>][product_id]">
                <option value="">Pilih produk</option>
                <?php foreach ($products as $product): ?>
                    <?php
                    $selected = (string) ($row['product_id'] ?? '') === (string) ($product['product_id'] ?? $product['id'] ?? '');
                    $productId = (int) ($product['product_id'] ?? $product['id'] ?? 0);
                    $label = (string) ($product['product_code'] ?? $product['code'] ?? '');
                    $name = (string) ($product['product_name'] ?? $product['name'] ?? 'Produk');
                    $stockText = (string) ($product['total_quantity'] ?? 0);
                    ?>
                    <option value="<?= $productId ?>"<?= $selected ? ' selected' : '' ?>>
                        <?= Helper::e($label) ?> - <?= Helper::e($name) ?> (stok: <?= Helper::e($stockText) ?>)
                    </option>
                <?php endforeach; ?>
            </select><br>
            <?php if (!empty($errors['items_' . $i . '_product_id'])): ?><small><?= Helper::e((string) $errors['items_' . $i . '_product_id']) ?></small><br><?php endif; ?>

            <label>Quantity</label><br>
            <input type="text" name="items[<?= (int) $i ?>][quantity]" value="<?= Helper::e((string) ($row['quantity'] ?? '')) ?>"><br>
            <?php if (!empty($errors['items_' . $i . '_quantity'])): ?><small><?= Helper::e((string) $errors['items_' . $i . '_quantity']) ?></small><br><?php endif; ?>

            <label>Harga Satuan</label><br>
            <input type="text" name="items[<?= (int) $i ?>][unit_price]" value="<?= Helper::e((string) ($row['unit_price'] ?? '')) ?>"><br>
            <?php if (!empty($errors['items_' . $i . '_unit_price'])): ?><small><?= Helper::e((string) $errors['items_' . $i . '_unit_price']) ?></small><br><?php endif; ?>

            <label>Diskon Item</label><br>
            <input type="text" name="items[<?= (int) $i ?>][discount]" value="<?= Helper::e((string) ($row['discount'] ?? '0')) ?>"><br>
            <?php if (!empty($errors['items_' . $i . '_discount'])): ?><small><?= Helper::e((string) $errors['items_' . $i . '_discount']) ?></small><br><?php endif; ?>
        </fieldset>
    <?php endforeach; ?>

    <button type="submit">Simpan Sales Order</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>
