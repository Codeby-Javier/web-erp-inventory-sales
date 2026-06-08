<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; require __DIR__ . '/../layout/sidebar.php'; $item = $product ?? []; ?>
<h2>Form Produk - <?= Helper::e((string) $mode) ?></h2>
<form method="post" action="<?= APP_URL ?>/product/<?= $mode === 'edit' ? 'update/' . (int) ($item['id'] ?? 0) : 'store' ?>">
<?= $csrfField ?><label>Kode</label><br><input type="text" name="code" value="<?= Helper::e((string) (($old['code'] ?? null) ?? ($item['code'] ?? ''))) ?>"><br>
<label>Nama</label><br><input type="text" name="name" value="<?= Helper::e((string) (($old['name'] ?? null) ?? ($item['name'] ?? ''))) ?>"><br>
<label>Unit</label><br><input type="number" name="unit_id" value="<?= (int) (($old['unit_id'] ?? null) ?? ($item['unit_id'] ?? 0)) ?>"><br>
<label>Kategori</label><br><input type="number" name="category_id" value="<?= (int) (($old['category_id'] ?? null) ?? ($item['category_id'] ?? 0)) ?>"><br>
<label>Harga Beli</label><br><input type="text" name="buy_price" value="<?= Helper::e((string) (($old['buy_price'] ?? null) ?? ($item['buy_price'] ?? 0))) ?>"><br>
<label>Harga Jual</label><br><input type="text" name="sell_price" value="<?= Helper::e((string) (($old['sell_price'] ?? null) ?? ($item['sell_price'] ?? 0))) ?>"><br>
<label>Min Stock</label><br><input type="text" name="min_stock" value="<?= Helper::e((string) (($old['min_stock'] ?? null) ?? ($item['min_stock'] ?? 0))) ?>"><br>
<label>Aktif</label><br><input type="number" name="is_active" value="<?= (int) (($old['is_active'] ?? null) ?? ($item['is_active'] ?? 1)) ?>"><br><br>
<button type="submit">Simpan</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>