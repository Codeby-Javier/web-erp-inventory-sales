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
<h2>Stock Out</h2>
<p><a href="<?= APP_URL ?>/stock/overview">Kembali ke overview stok</a></p>

<form method="post" action="<?= APP_URL ?>/stock/process-out">
    <?= $csrfField ?>

    <label>Produk</label><br>
    <select name="product_id" required>
        <option value="">Pilih produk</option>
        <?php foreach ($products as $product): ?>
            <?php
            $productId = (int) ($product['product_id'] ?? $product['id'] ?? 0);
            $selected = (string) ($old['product_id'] ?? '') === (string) $productId;
            $code = (string) ($product['product_code'] ?? $product['code'] ?? '');
            $name = (string) ($product['product_name'] ?? $product['name'] ?? 'Produk');
            $stock = (string) ($product['total_quantity'] ?? 0);
            ?>
            <option value="<?= $productId ?>"<?= $selected ? ' selected' : '' ?>>
                <?= Helper::e($code) ?> - <?= Helper::e($name) ?> (stok: <?= Helper::e($stock) ?>)
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

    <label>Catatan</label><br>
    <textarea name="notes" rows="3" cols="60"><?= Helper::e((string) ($old['notes'] ?? '')) ?></textarea><br><br>

    <button type="submit">Simpan Stock Out</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>
