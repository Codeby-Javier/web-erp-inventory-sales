<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= Helper::e((string) $title) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= APP_URL ?>/category/create" class="btn btn-sm btn-primary">Tambah Kategori</a>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td class="fw-bold"><?= Helper::e($item['name']) ?></td>
                                <td>
                                    <?php if (isset($item['is_active'])): ?>
                                        <span class="badge rounded-pill bg-<?= $item['is_active'] == 1 ? 'success' : 'secondary' ?>">
                                            <?= $item['is_active'] == 1 ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-info">Sistem</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= APP_URL ?>/category/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="<?= APP_URL ?>/category/delete/<?= $item['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
