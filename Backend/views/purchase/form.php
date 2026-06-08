<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$suppliers = is_array($suppliers ?? null) ? $suppliers : [];
$locations = is_array($locations ?? null) ? $locations : [];
$products = is_array($products ?? null) ? $products : [];
$oldItems = is_array($old['items'] ?? null) ? $old['items'] : [];

if ($oldItems === []) {
    $oldItems = array_fill(0, 5, []);
}
?>
<h2>Form Purchase Order</h2>
<p><a href="<?= APP_URL ?>/purchase">Kembali ke daftar PO</a></p>

<?php if ($errors !== []): ?>
<p>Terdapat data yang belum valid. Periksa field yang ditandai di bawah ini.</p>
<?php endif; ?>

<form method="post" action="<?= APP_URL ?>/purchase/store">
    <?= $csrfField ?>

    <label>Supplier</label><br>
    <select name="supplier_id">
        <option value="">Tanpa supplier</option>
        <?php foreach ($suppliers as $supplier): ?>
            <?php $selected = (string) ($old['supplier_id'] ?? '') === (string) ($supplier['id'] ?? ''); ?>
            <option value="<?= (int) ($supplier['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($supplier['name'] ?? 'Supplier')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <?php if (!empty($errors['supplier_id'])): ?><small><?= Helper::e((string) $errors['supplier_id']) ?></small><br><?php endif; ?>

    <label>Lokasi</label><br>
    <select name="location_id" required>
        <option value="">Pilih lokasi</option>
        <?php foreach ($locations as $location): ?>
            <?php $selected = (string) ($old['location_id'] ?? '') === (string) ($location['id'] ?? ''); ?>
            <option value="<?= (int) ($location['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($location['name'] ?? 'Lokasi')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <?php if (!empty($errors['location_id'])): ?><small><?= Helper::e((string) $errors['location_id']) ?></small><br><?php endif; ?>

    <label>Tanggal Order</label><br>
    <input type="date" name="order_date" value="<?= Helper::e((string) ($old['order_date'] ?? date('Y-m-d'))) ?>" required><br>
    <?php if (!empty($errors['order_date'])): ?><small><?= Helper::e((string) $errors['order_date']) ?></small><br><?php endif; ?>

    <label>Catatan</label><br>
    <textarea name="notes" rows="3" cols="60"><?= Helper::e((string) ($old['notes'] ?? '')) ?></textarea><br><br>

    <h3>Item Purchase</h3>
    <?php if (!empty($errors['items'])): ?><small><?= Helper::e((string) $errors['items']) ?></small><br><br><?php endif; ?>

    <?php foreach ($oldItems as $i => $row): ?>
        <fieldset style="margin-bottom:12px;">
            <legend>Item <?= (int) $i + 1 ?></legend>

            <label>Produk</label><br>
            <select name="items[<?= (int) $i ?>][product_id]">
                <option value="">Pilih produk</option>
                <?php foreach ($products as $product): ?>
                    <?php $selected = (string) ($row['product_id'] ?? '') === (string) ($product['id'] ?? ''); ?>
                    <option value="<?= (int) ($product['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                        <?= Helper::e((string) ($product['code'] ?? '')) ?> - <?= Helper::e((string) ($product['name'] ?? 'Produk')) ?>
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

            <label>Batch No</label><br>
            <input type="text" name="items[<?= (int) $i ?>][batch_no]" value="<?= Helper::e((string) ($row['batch_no'] ?? '')) ?>"><br>

            <label>Expired Date</label><br>
            <input type="date" name="items[<?= (int) $i ?>][expired_date]" value="<?= Helper::e((string) ($row['expired_date'] ?? '')) ?>"><br>
        </fieldset>
    <?php endforeach; ?>

    <button type="submit">Simpan Purchase Order</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>
