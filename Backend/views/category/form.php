<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= Helper::e((string) $title) ?> - <?= $mode === 'edit' ? 'Edit' : 'Tambah' ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= APP_URL ?>/category" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-body p-4">
                <form method="post" action="<?= APP_URL ?>/category/<?= $mode === 'edit' ? 'update/' . (int) ($item['id'] ?? 0) : 'store' ?>">
                    <?= $csrfField ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= Helper::e((string) (($old['name'] ?? null) ?? ($item['name'] ?? ''))) ?>" required autofocus>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= Helper::e($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <?php $currentStatus = (int) (($old['is_active'] ?? null) ?? ($item['is_active'] ?? 1)); ?>
                            <option value="1" <?= $currentStatus === 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= $currentStatus === 0 ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
