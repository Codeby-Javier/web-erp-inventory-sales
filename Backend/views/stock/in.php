<?php

declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$products = is_array($products ?? null) ? $products : [];
$locations = is_array($locations ?? null) ? $locations : [];
$csrfField = (string) ($csrfField ?? '');
?>
<h2>Stock In</h2>
<p><a href="<?= APP_URL ?>/stock/overview">Kembali ke overview stok</a></p>

<form method="post" action="<?= APP_URL ?>/stock/process-in">
    <?= $csrfField ?>

    <label>Produk</label><br>
    <select name="product_id" required>
        <option value="">Pilih produk</option>
        <?php foreach ($products as $product): ?>
            <?php $selected = (string) ($old['product_id'] ?? '') === (string) ($product['id'] ?? ''); ?>
            <option value="<?= (int) ($product['id'] ?? 0) ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e((string) ($product['code'] ?? '')) ?> - <?= Helper::e((string) ($product['name'] ?? 'Produk')) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <?php if (!empty($errors['product_id'])): ?><small><?= Helper::e((string) $errors['product_id']) ?></small><br><?php endif; ?>

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

    <label>Quantity</label><br>
    <input type="text" name="quantity" value="<?= Helper::e((string) ($old['quantity'] ?? '')) ?>" required><br>
    <?php if (!empty($errors['quantity'])): ?><small><?= Helper::e((string) $errors['quantity']) ?></small><br><?php endif; ?>

    <label>Harga Beli</label><br>
    <input type="text" name="buy_price" value="<?= Helper::e((string) ($old['buy_price'] ?? '0')) ?>"><br>
    <?php if (!empty($errors['buy_price'])): ?><small><?= Helper::e((string) $errors['buy_price']) ?></small><br><?php endif; ?>

    <label>Batch No</label><br>
    <input type="text" name="batch_no" value="<?= Helper::e((string) ($old['batch_no'] ?? '')) ?>"><br>

    <label>Expired Date</label><br>
    <input type="date" name="expired_date" value="<?= Helper::e((string) ($old['expired_date'] ?? '')) ?>"><br>

    <label>Catatan</label><br>
    <textarea name="notes" rows="3" cols="60"><?= Helper::e((string) ($old['notes'] ?? '')) ?></textarea><br><br>

    <button type="submit">Simpan Stock In</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>
