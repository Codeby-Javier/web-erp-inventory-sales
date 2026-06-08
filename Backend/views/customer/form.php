<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; require __DIR__ . '/../layout/sidebar.php'; ?>
<h2><?= Helper::e((string) $title) ?> - <?= Helper::e((string) $mode) ?></h2>
<form method="post" action="<?= APP_URL ?>/<?= Helper::e((string) basename(__DIR__)) ?>/<?= $mode === 'edit' ? 'update/' . (int) ($item['id'] ?? 0) : 'store' ?>">
    <?= $csrfField ?>
    <label>Nama</label><br>
    <input type="text" name="name" value="<?= Helper::e((string) (($old['name'] ?? null) ?? ($item['name'] ?? ''))) ?>"><br>
    <label>Aktif</label><br>
    <input type="number" name="is_active" value="<?= (int) (($old['is_active'] ?? null) ?? ($item['is_active'] ?? 1)) ?>"><br><br>
    <button type="submit">Simpan</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>