<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Produk</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= APP_URL ?>/product/create" class="btn btn-sm btn-primary">Tambah Produk</a>
    </div>
</div>

<div class="card shadow border-0 mb-4">
    <div class="card-body">
        <form method="get" action="<?= APP_URL ?>/product" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama produk..." value="<?= Helper::e($filters['search']) ?>">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= Helper::e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga Jual</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data produk</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><code class="text-primary"><?= Helper::e($p['code']) ?></code></td>
                                <td>
                                    <div class="fw-bold"><?= Helper::e($p['name']) ?></div>
                                    <small class="text-muted"><?= Helper::e($p['unit_name']) ?></small>
                                </td>
                                <td><?= Helper::e($p['category_name'] ?? '-') ?></td>
                                <td>
                                    <span class="fw-bold <?= $p['stock_quantity'] <= $p['min_stock'] ? 'text-danger' : '' ?>">
                                        <?= number_format((float)$p['stock_quantity']) ?>
                                    </span>
                                </td>
                                <td><?= Helper::formatRupiah((float)$p['sell_price']) ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= $p['is_active'] == 1 ? 'success' : 'secondary' ?>">
                                        <?= $p['is_active'] == 1 ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= APP_URL ?>/product/edit/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="<?= APP_URL ?>/product/delete/<?= $p['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                        <?= $csrfField ?>
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
