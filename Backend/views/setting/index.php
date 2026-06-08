<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; require __DIR__ . '/../layout/sidebar.php'; ?>
<h2>Setting</h2>
<form method="post" action="<?= APP_URL ?>/setting/update">
    <?= $csrfField ?>
    <?php foreach ($settings as $setting): ?>
        <label><?= Helper::e((string) $setting['key_name']) ?></label><br>
        <input type="text" name="settings[<?= (int) $setting['id'] ?>]" value="<?= Helper::e((string) $setting['value']) ?>"><br>
    <?php endforeach; ?>
    <br><button type="submit">Simpan</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>