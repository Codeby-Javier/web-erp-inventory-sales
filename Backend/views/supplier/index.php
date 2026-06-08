<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; require __DIR__ . '/../layout/sidebar.php'; ?>
<h2><?= Helper::e((string) $title) ?></h2>
<p><a href="<?= APP_URL ?>/<?= Helper::e((string) basename(__DIR__)) ?>/create">Tambah</a></p>
<pre><?php print_r($items); ?></pre>
<?php require __DIR__ . '/../layout/footer.php'; ?>